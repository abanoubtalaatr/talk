<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private const DAILY_MESSAGE_LIMIT = 3;

    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Send a message to another user.
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

        // Check daily message limit (3 messages per day)
        $todayCount = $user->sentMessages()
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= self::DAILY_MESSAGE_LIMIT) {
            return response()->json([
                'message' => 'You have reached your daily message limit (' . self::DAILY_MESSAGE_LIMIT . ' messages per day).',
            ], 429);
        }

        // Check if last message to the same receiver was less than 24 hours ago
        $lastMessageToReceiver = $user->sentMessages()
            ->where('receiver_id', $validated['receiver_id'])
            ->latest()
            ->first();

        if ($lastMessageToReceiver && $lastMessageToReceiver->created_at->diffInHours(now()) < 24) {
            return response()->json([
                'message' => 'You must wait 24 hours before sending another message to this user.',
            ], 429);
        }

        $message = $user->sentMessages()->create([
            'receiver_id' => $validated['receiver_id'],
            'content' => $validated['content'],
        ]);

        // Log activity
        $this->activityLogService->logMessageSent($user, $request);

        $message->load(['sender', 'receiver']);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MessageResource($message),
        ], 201);
    }

    /**
     * Get the last 3 messages for the authenticated user.
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
