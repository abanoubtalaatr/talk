<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\StoreReactionRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReactionResource;
use App\Models\Post;
use App\Models\Reaction;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a new post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $post = $user->posts()->create([
            'content' => $validated['content'],
            'is_anonymous' => $validated['is_anonymous'] ?? false,
        ]);

        // Attach categories
        $post->categories()->attach($validated['category_ids']);

        // Log activity
        $this->activityLogService->logPostCreated($user, $request);

        $post->load(['user', 'categories'])
            ->loadCount(['likes', 'stars', 'reactions', 'comments']);

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => new PostResource($post),
        ], 201);
    }

    /**
     * List posts relevant to the authenticated user's categories.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $posts = Post::forUserCategories($user)
            ->with(['user', 'categories'])
            ->withCount(['likes', 'stars', 'reactions', 'comments'])
            ->latest()
            ->paginate(15);

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
     * Get the best posts ordered by total reactions (stars + likes).
     */
    public function best(): JsonResponse
    {
        $posts = Post::best()
            ->with(['user', 'categories'])
            ->withCount(['comments'])
            ->paginate(15);

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
     * Get a single post with its comments (multi-level).
     */
    public function show(Post $post): JsonResponse
    {
        $post->load([
            'user',
            'categories',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user', 'allReplies.user'])
                    ->latest();
            },
        ])->loadCount(['likes', 'stars', 'reactions', 'comments']);

        return response()->json([
            'post' => new PostResource($post),
        ]);
    }

    /**
     * Add a comment or reply to a post.
     */
    public function comment(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // If replying, verify parent comment belongs to the same post
        if (!empty($validated['parent_id'])) {
            $parentComment = $post->comments()->find($validated['parent_id']);
            if (!$parentComment) {
                return response()->json([
                    'message' => 'The parent comment does not belong to this post.',
                ], 422);
            }
        }

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Log activity
        $this->activityLogService->logCommentCreated($user, $request);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Add or toggle a reaction (like/star) on a post.
     */
    public function reaction(StoreReactionRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Toggle reaction: remove if exists, create if not
        $existing = Reaction::where([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'type' => $validated['type'],
        ])->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'message' => ucfirst($validated['type']) . ' removed.',
                'action' => 'removed',
                'likes_count' => $post->likes()->count(),
                'stars_count' => $post->stars()->count(),
            ]);
        }

        $reaction = Reaction::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'type' => $validated['type'],
        ]);

        // Log activity
        $this->activityLogService->logReactionAdded($user, $validated['type'], $request);

        return response()->json([
            'message' => ucfirst($validated['type']) . ' added.',
            'action' => 'added',
            'reaction' => new ReactionResource($reaction),
            'likes_count' => $post->likes()->count(),
            'stars_count' => $post->stars()->count(),
        ], 201);
    }
}
