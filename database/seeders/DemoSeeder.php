<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo users, posts, comments, and reactions.
     */
    public function run(): void
    {
        $categories = Category::all();

        // Create demo users
        $users = collect();
        $usernames = ['alice', 'bob', 'charlie', 'diana', 'eve'];

        foreach ($usernames as $index => $username) {
            $user = User::factory()->create([
                'username' => $username,
                'referral_user_id' => $index > 0 ? $users->first()->id : null,
            ]);

            // Assign all default categories to each user
            $user->categories()->attach($categories->pluck('id'));
            $users->push($user);
        }

        // Create demo posts
        foreach ($users as $user) {
            $post = Post::create([
                'user_id' => $user->id,
                'content' => "Hello from {$user->username}! This is a demo post about life and connections.",
                'is_anonymous' => false,
            ]);

            // Attach random categories
            $post->categories()->attach(
                $categories->random(rand(1, $categories->count()))->pluck('id')
            );

            // Add comments from other users
            $otherUsers = $users->where('id', '!=', $user->id)->take(2);
            foreach ($otherUsers as $commenter) {
                $comment = Comment::create([
                    'user_id' => $commenter->id,
                    'post_id' => $post->id,
                    'content' => "Great post, {$user->username}! - from {$commenter->username}",
                ]);

                // Add a reply
                Comment::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'parent_id' => $comment->id,
                    'content' => "Thanks {$commenter->username}!",
                ]);
            }

            // Add reactions from other users
            foreach ($users->where('id', '!=', $user->id)->take(3) as $reactor) {
                Reaction::create([
                    'user_id' => $reactor->id,
                    'post_id' => $post->id,
                    'type' => ['like', 'star'][array_rand(['like', 'star'])],
                ]);
            }
        }
    }
}
