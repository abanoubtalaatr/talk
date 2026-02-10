<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(
            ['name' => 'Free'],
            [
                'is_free' => true,
                'new_chats_per_day' => 3,
                'messages_per_day' => 3,
                'send_new_msg_per_day' => 3,
                'posts_per_day' => -1,
                'post_chars' => 300,
                'message_chars' => 200,
                'topic_change_days' => 7,
                'topics_count' => 3,
                'open_chats' => 0,
            ]
        );
    }
}
