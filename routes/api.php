<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardCategoryController;
use App\Http\Controllers\Api\DashboardCommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DashboardMessageController;
use App\Http\Controllers\Api\DashboardPaymentController;
use App\Http\Controllers\Api\DashboardPlanController;
use App\Http\Controllers\Api\DashboardPostController;
use App\Http\Controllers\Api\DashboardUserController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::put('/users/categories', [CategoryController::class, 'updateUserCategories']);

    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/best', [PostController::class, 'best']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::post('/posts/{post}/comment', [PostController::class, 'comment']);
    Route::post('/posts/{post}/reaction', [PostController::class, 'reaction']);

    // Messages / Chat (limit: 3 messages per day total to any people)
    Route::get('/messages/quota', [MessageController::class, 'quota']);
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/conversation/{user}', [MessageController::class, 'conversation']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/last', [MessageController::class, 'last']);

    // Activity Logs
    Route::get('/users/{user}/activity', [ActivityLogController::class, 'index']);

    // Dashboard (admin)
    Route::prefix('dashboard')->group(function () {
        Route::apiResource('categories', DashboardCategoryController::class);
        Route::apiResource('plans', DashboardPlanController::class);

        // Users: search by username, display_name, id; plan, reputation, invited by, status; ban/unban
        Route::get('/users', [DashboardUserController::class, 'index']);
        Route::post('/users/{user}/ban', [DashboardUserController::class, 'ban']);
        Route::post('/users/{user}/unban', [DashboardUserController::class, 'unban']);

        // Messages: list (user-to-user, last message + date), delete whole chat (POST body: user_id, other_user_id)
        Route::get('/messages', [DashboardMessageController::class, 'index']);
        Route::post('/messages/delete-thread', [DashboardMessageController::class, 'destroyThread']);

        // Posts: search by created_by, content, id; featured, hide, delete, dismiss report
        Route::get('/posts', [DashboardPostController::class, 'index']);
        Route::post('/posts/{post}/featured', [DashboardPostController::class, 'featured']);
        Route::post('/posts/{post}/unfeatured', [DashboardPostController::class, 'unfeatured']);
        Route::post('/posts/{post}/hide', [DashboardPostController::class, 'hide']);
        Route::post('/posts/{post}/unhide', [DashboardPostController::class, 'unhide']);
        Route::post('/posts/{post}/dismiss-report', [DashboardPostController::class, 'dismissReport']);
        Route::delete('/posts/{post}', [DashboardPostController::class, 'destroy']);

        // Comments: search by username, content, post_id; filter reported; delete, dismiss report
        Route::get('/comments', [DashboardCommentController::class, 'index']);
        Route::delete('/comments/{comment}', [DashboardCommentController::class, 'destroy']);
        Route::post('/comments/{comment}/dismiss-report', [DashboardCommentController::class, 'dismissReport']);

        // Payments: search by name, id, user_id; filter under_review; approve, disapprove, delete
        Route::get('/payments', [DashboardPaymentController::class, 'index']);
        Route::post('/payments/{payment}/approve', [DashboardPaymentController::class, 'approve']);
        Route::post('/payments/{payment}/disapprove', [DashboardPaymentController::class, 'disapprove']);
        Route::delete('/payments/{payment}', [DashboardPaymentController::class, 'destroy']);

        Route::get('/best-posts', [DashboardController::class, 'bestPosts']);
    });
});
