<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Recipe;
use App\Models\Comment;
use App\Policies\RecipePolicy;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Recipe::class => RecipePolicy::class,
        Comment::class => CommentPolicy::class,
    ];
    public function boot(): void
    {
        //
    }
}
