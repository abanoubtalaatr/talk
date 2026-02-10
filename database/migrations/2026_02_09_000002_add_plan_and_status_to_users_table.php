<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('mac_address')->constrained('plans')->nullOnDelete();
            $table->string('display_name')->nullable()->after('username');
            $table->timestamp('banned_at')->nullable()->after('last_login_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'display_name', 'banned_at']);
        });
    }
};
