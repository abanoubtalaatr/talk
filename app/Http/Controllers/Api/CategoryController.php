<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCategoriesRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * List all categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Update the authenticated user's preferred categories (min 3).
     */
    public function updateUserCategories(UpdateCategoriesRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->categories()->sync($validated['category_ids']);

        // Log activity
        $this->activityLogService->logCategoriesUpdated($user, $request);

        return response()->json([
            'message' => 'Categories updated successfully.',
            'categories' => CategoryResource::collection($user->categories),
        ]);
    }
}
