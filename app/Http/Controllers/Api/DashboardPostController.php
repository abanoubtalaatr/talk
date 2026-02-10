<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPostController extends Controller
{
    /**
     * List posts. Search by id, content, created_by (username or user id). Filter. Show date, type (anonymous), comments count. Actions: featured, hide, delete, dismiss report.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['user', 'categories'])
            ->withCount(['likes', 'stars', 'reactions', 'comments']);

        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->filled('content')) {
            $query->where('content', 'like', '%' . $request->input('content') . '%');
        }
        if ($request->filled('created_by')) {
            $term = $request->input('created_by');
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('username', 'like', "%{$term}%")
                    ->orWhere('display_name', 'like', "%{$term}%")
                    ->orWhere('id', $term);
            });
        }
        if ($request->filled('reported') && $request->boolean('reported')) {
            $query->whereNotNull('reported_at');
        }

        $posts = $query->latest()->paginate(20);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function featured(Post $post): JsonResponse
    {
        $post->update(['is_featured' => true]);

        return response()->json([
            'message' => 'Post set as featured.',
            'post' => new PostResource($post->load(['user', 'categories'])->loadCount(['likes', 'stars', 'reactions', 'comments'])),
        ]);
    }

    public function unfeatured(Post $post): JsonResponse
    {
        $post->update(['is_featured' => false]);

        return response()->json([
            'message' => 'Post unfeatured.',
            'post' => new PostResource($post->load(['user', 'categories'])->loadCount(['likes', 'stars', 'reactions', 'comments'])),
        ]);
    }

    public function hide(Post $post): JsonResponse
    {
        $post->update(['is_hidden' => true]);

        return response()->json([
            'message' => 'Post hidden.',
            'post' => new PostResource($post->load(['user', 'categories'])->loadCount(['likes', 'stars', 'reactions', 'comments'])),
        ]);
    }

    public function unhide(Post $post): JsonResponse
    {
        $post->update(['is_hidden' => false]);

        return response()->json([
            'message' => 'Post visible again.',
            'post' => new PostResource($post->load(['user', 'categories'])->loadCount(['likes', 'stars', 'reactions', 'comments'])),
        ]);
    }

    public function dismissReport(Post $post): JsonResponse
    {
        $post->update(['reported_at' => null]);

        return response()->json([
            'message' => 'Report dismissed.',
            'post' => new PostResource($post->load(['user', 'categories'])->loadCount(['likes', 'stars', 'reactions', 'comments'])),
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ], 200);
    }
}
