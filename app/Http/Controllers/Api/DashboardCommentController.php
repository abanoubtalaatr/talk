<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardCommentController extends Controller
{
    /**
     * List comments. Search by username, content, post_id. Filter by reported.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with(['user', 'post']);

        if ($request->filled('username')) {
            $query->whereHas('user', fn ($q) => $q->where('username', 'like', '%' . $request->input('username') . '%'));
        }
        if ($request->filled('content')) {
            $query->where('content', 'like', '%' . $request->input('content') . '%');
        }
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->input('post_id'));
        }
        if ($request->filled('reported') && $request->boolean('reported')) {
            $query->whereNotNull('reported_at');
        }

        $comments = $query->latest()->paginate(20);

        return response()->json([
            'comments' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ], 200);
    }

    public function dismissReport(Comment $comment): JsonResponse
    {
        $comment->update(['reported_at' => null]);

        return response()->json([
            'message' => 'Report dismissed.',
            'comment' => new CommentResource($comment->load(['user', 'post'])),
        ]);
    }
}
