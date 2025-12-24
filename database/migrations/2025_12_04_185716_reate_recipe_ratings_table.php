<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_08_000000_create_recipe_ratings_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ratings');
    }
};
