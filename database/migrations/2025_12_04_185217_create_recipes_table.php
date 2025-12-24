<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_02_000000_create_recipes_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $table->unsignedInteger('prep_time_minutes')->default(0);
            $table->unsignedInteger('cook_time_minutes')->default(0);
            $table->unsignedInteger('servings')->default(1);
            $table->enum('visibility', ['private', 'public', 'link'])->default('private');
            $table->string('main_image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
