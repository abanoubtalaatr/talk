<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Private channel for a user: only that user can subscribe (for real-time messages).
| Private channel for a category: users who have that category can subscribe
| (for "new message in network" to users with shared categories).
|
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('category.{id}', function ($user, $id) {
    return $user->categories()->where('categories.id', $id)->exists();
});
