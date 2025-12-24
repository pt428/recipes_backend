<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2024_01_04_000000_create_steps_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order_index');
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
