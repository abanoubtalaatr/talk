<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 3 users with the same categories for testing:
 * - Post broadcast (post.created on private-category.*)
 * - Message broadcast (message.sent, message.sent_to_network)
 * - Chat and posts API
 *
 * Users: test1 / test2 / test3 — password: "password"
 * All share categories: Love, Friendship, Dreams
 */
class BroadcastTestSeeder extends Seeder
{
    private const PASSWORD = 'password';

    public function run(): void
    {
        $categories = Category::whereIn('name', ['Love', 'Friendship', 'Dreams'])->orderBy('name')->get();

        if ($categories->count() < 3) {
            $this->call(CategorySeeder::class);
            $categories = Category::whereIn('name', ['Love', 'Friendship', 'Dreams'])->orderBy('name')->get();
        }

        $categoryIds = $categories->pluck('id')->all();

        $users = collect(['test1', 'test2', 'test3'])->map(function (string $username) use ($categoryIds) {
            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'password' => Hash::make(self::PASSWORD),
                    'points' => 0,
                ]
            );

            $user->categories()->sync($categoryIds);
            return $user;
        });

        // Posts so each user has something in the feed (same categories = all receive broadcast)
        $postContents = [
            'Post from test1: Sharing thoughts on friendship and dreams.',
            'Post from test2: Love and connection matter.',
            'Post from test3: Dream big, connect often.',
        ];

        foreach ($users as $index => $user) {
            $post = Post::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'content' => $postContents[$index],
                ],
                ['is_anonymous' => false]
            );

            if ($post->wasRecentlyCreated) {
                $post->categories()->sync($categoryIds);
            }
        }

        // Messages between users (backdated so they don't affect 24h / 3-per-day limits when testing)
        $yesterday = now()->subDay();

        $messages = [
            [$users[0]->id, $users[1]->id, 'Hi test2! Ready to test broadcast?'],
            [$users[1]->id, $users[2]->id, 'Hey test3, checking real-time messages.'],
            [$users[2]->id, $users[0]->id, 'Test1, message for conversation list.'],
        ];

        foreach ($messages as [$senderId, $receiverId, $content]) {
            $msg = Message::firstOrCreate(
                ['sender_id' => $senderId, 'receiver_id' => $receiverId],
                ['content' => $content]
            );
            if ($msg->wasRecentlyCreated) {
                $msg->created_at = $yesterday;
                $msg->updated_at = $yesterday;
                $msg->saveQuietly();
            }
        }

        $this->command->info('Broadcast test users ready: test1, test2, test3 (password: password). Same categories: Love, Friendship, Dreams.');
    }
}
