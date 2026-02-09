<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * List all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount(['posts', 'users'])->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * List all users with points, last login, referral link.
     */
    public function users(): JsonResponse
    {
        $users = User::withStats()
            ->with('categories')
            ->latest()
            ->paginate(20);

        return response()->json([
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * All posts with user info, reactions, comments.
     */
    public function posts(): JsonResponse
    {
        $posts = Post::with(['user', 'categories'])
            ->withCount(['likes', 'stars', 'reactions', 'comments'])
            ->latest()
            ->paginate(20);

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

    /**
     * Last messages per user.
     */
    public function messages(): JsonResponse
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
     * Best posts ordered by total reactions (stars + likes).
     */
    public function bestPosts(): JsonResponse
    {
        $posts = Post::best()
            ->with(['user', 'categories'])
            ->withCount(['comments'])
            ->paginate(20);

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
}
