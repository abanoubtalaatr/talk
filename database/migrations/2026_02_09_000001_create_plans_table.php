<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Free"
            $table->boolean('is_free')->default(true);
            $table->unsignedSmallInteger('new_chats_per_day')->default(3);
            $table->unsignedSmallInteger('messages_per_day')->default(3);
            $table->unsignedSmallInteger('send_new_msg_per_day')->default(3);
            $table->integer('posts_per_day')->default(-1); // -1 = unlimited
            $table->unsignedSmallInteger('post_chars')->default(300);
            $table->unsignedSmallInteger('message_chars')->default(200);
            $table->unsignedSmallInteger('topic_change_days')->default(7);
            $table->unsignedSmallInteger('topics_count')->default(3);
            $table->unsignedSmallInteger('open_chats')->default(0); // 0 = unlimited
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
