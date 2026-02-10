<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardMessageController extends Controller
{
    /**
     * List message threads (user-to-user). Each item: last message between two users, with sender/receiver and date.
     */
    public function index(Request $request): JsonResponse
    {
        $messages = Message::with(['sender', 'receiver'])
            ->latest()
            ->paginate(20);

        return response()->json([
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
     * Delete all messages between two users (whole chat).
     */
    public function destroyThread(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'other_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $deleted = Message::where(function ($q) use ($request) {
            $q->where('sender_id', $request->user_id)->where('receiver_id', $request->other_user_id)
                ->orWhere('sender_id', $request->other_user_id)->where('receiver_id', $request->user_id);
        })->delete();

        return response()->json([
            'message' => "Chat deleted. {$deleted} messages removed.",
            'deleted_count' => $deleted,
        ], 200);
    }
}
