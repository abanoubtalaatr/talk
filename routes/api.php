<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
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

    // Messages
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/last', [MessageController::class, 'last']);

    // Activity Logs
    Route::get('/users/{user}/activity', [ActivityLogController::class, 'index']);

    // Dashboard / Analytics
    Route::prefix('dashboard')->group(function () {
        Route::get('/categories', [DashboardController::class, 'categories']);
        Route::get('/users', [DashboardController::class, 'users']);
        Route::get('/posts', [DashboardController::class, 'posts']);
        Route::get('/messages', [DashboardController::class, 'messages']);
        Route::get('/best-posts', [DashboardController::class, 'bestPosts']);
    });
});
