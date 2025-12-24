<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_07_000000_create_recipe_tag_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_tag', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['recipe_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_tag');
    }
};
