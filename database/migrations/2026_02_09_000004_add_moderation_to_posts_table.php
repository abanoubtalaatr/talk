<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_anonymous');
            $table->boolean('is_hidden')->default(false)->after('is_featured');
            $table->timestamp('reported_at')->nullable()->after('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_hidden', 'reported_at']);
        });
    }
};
