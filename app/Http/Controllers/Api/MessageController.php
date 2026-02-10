<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\MessageSentToNetwork;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /** Max 3 new conversations (first message to a user) per day. Replies in existing chats are unlimited. */
    private const DAILY_NEW_MESSAGE_LIMIT = 3;

    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Get quota for new conversations: 3 per day. When you send one, it broadcasts to receiver and to all users with the same category.
     * Replies (continuing an existing chat) do not count; they are unlimited.
     */
    public function quota(Request $request): JsonResponse
    {
        $user = $request->user();

        $newConversationsStartedToday = $this->countNewConversationStartsToday($user);
        $remaining = max(0, self::DAILY_NEW_MESSAGE_LIMIT - $newConversationsStartedToday);

        return response()->json([
            'new_conversations_started_today' => $newConversationsStartedToday,
            'limit' => self::DAILY_NEW_MESSAGE_LIMIT,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Count how many "first message in thread" (new conversation) the user sent today.
     */
    private function countNewConversationStartsToday(User $user): int
    {
        return Message::where('sender_id', $user->id)
            ->whereDate('created_at', today())
            ->whereRaw('created_at = (
                SELECT MIN(m2.created_at) FROM messages m2
                WHERE (m2.sender_id = ? AND m2.receiver_id = messages.receiver_id)
                   OR (m2.sender_id = messages.receiver_id AND m2.receiver_id = ?)
            )', [$user->id, $user->id])
            ->count();
    }

    /**
     * Send a message (new conversation or reply). Use same endpoint to continue chatting with one person.
     * - New conversation: first message to that user. Limited to 3 per day; broadcasts to receiver and to all users with same category.
     * - Reply: continuing an existing chat. Unlimited; same broadcast.
     * - 24h rule: if you were the last sender to this user, wait 24h or for their reply before sending again.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Cannot send message to self
        if ($user->id === (int) $validated['receiver_id']) {
            return response()->json([
                'message' => 'You cannot send a message to yourself.',
            ], 422);
        }

        $receiverId = (int) $validated['receiver_id'];
        $threadExists = Message::where(function ($q) use ($user, $receiverId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $receiverId)
                ->orWhere('sender_id', $receiverId)->where('receiver_id', $user->id);
        })->exists();

        // Only limit "new" conversations (first message in thread). Replies are unlimited.
        if (! $threadExists) {
            $newStartsToday = $this->countNewConversationStartsToday($user);
            if ($newStartsToday >= self::DAILY_NEW_MESSAGE_LIMIT) {
                return response()->json([
                    'message' => 'You have reached your daily limit of ' . self::DAILY_NEW_MESSAGE_LIMIT . ' new conversations. Reply in existing chats to continue.',
                ], 429);
            }
        }

        // 24h rule: only block when YOU were the last sender. If they messaged you last, you can reply.
        $lastInThread = Message::where(function ($q) use ($user, $receiverId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $receiverId)
                ->orWhere('sender_id', $receiverId)->where('receiver_id', $user->id);
        })->latest()->first();

        if ($lastInThread && $lastInThread->sender_id === $user->id && $lastInThread->created_at->diffInHours(now()) < 24) {
            return response()->json([
                'message' => 'You must wait 24 hours before sending again to this user, or wait for their reply.',
            ], 429);
        }

        $message = $user->sentMessages()->create([
            'receiver_id' => $receiverId,
            'content' => $validated['content'],
        ]);

        $message->load(['sender', 'receiver']);

        // --- Broadcast (real-time for all users with same category) ---
        // 1. Receiver gets the full message on private-user.{receiver_id}
        broadcast(new MessageSent($message))->toOthers();

        // 2. Every user who shares at least one category with sender AND receiver gets "message in network" on their private-user.{id} (once per user)
        $receiver = $message->receiver;
        $sharedCategoryIds = $user->categories()
            ->whereIn('categories.id', $receiver->categories()->pluck('categories.id'))
            ->pluck('categories.id');

        $userIdsToNotify = User::whereHas('categories', fn ($q) => $q->whereIn('categories.id', $sharedCategoryIds))
            ->whereNotIn('id', [$user->id, $receiver->id])
            ->pluck('id');

        foreach ($userIdsToNotify as $targetUserId) {
            broadcast(new MessageSentToNetwork($message, $targetUserId))->toOthers();
        }

        $this->activityLogService->logMessageSent($user, $request);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MessageResource($message),
        ], 201);
    }

    /**
     * List conversations (chats) for the authenticated user.
     * Returns distinct conversation partners with last message, ordered by last message time.
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = Message::forUser($user->id)
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        $seen = [];
        $conversations = [];

        foreach ($messages as $m) {
            $otherId = $m->sender_id === $user->id ? $m->receiver_id : $m->sender_id;
            if (isset($seen[$otherId])) {
                continue;
            }
            $seen[$otherId] = true;
            $other = $m->sender_id === $user->id ? $m->receiver : $m->sender;
            $conversations[] = [
                'user' => $other,
                'last_message' => $m,
            ];
            if (count($conversations) >= 50) {
                break;
            }
        }

        return response()->json([
            'conversations' => ConversationResource::collection(collect($conversations)),
        ]);
    }

    /**
     * Get paginated messages between the authenticated user and the given user.
     */
    public function conversation(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json([
                'message' => 'Cannot load conversation with yourself.',
            ], 422);
        }

        $messages = Message::forUser($currentUser->id)
            ->where(function ($q) use ($currentUser, $user) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id)->where('receiver_id', $currentUser->id);
            })
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'user' => new UserResource($user),
            'messages' => MessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Get the last 3 messages sent by the authenticated user.
     */
    public function last(Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = $user->sentMessages()
            ->with(['sender', 'receiver'])
            ->latest()
            ->take(3)
            ->get();

        return response()->json([
            'messages' => MessageResource::collection($messages),
        ]);
    }
}
