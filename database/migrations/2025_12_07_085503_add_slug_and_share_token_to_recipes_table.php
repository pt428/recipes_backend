<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
            $table->string('share_token', 64)->unique()->nullable()->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['slug', 'share_token']);
        });
    }
};
