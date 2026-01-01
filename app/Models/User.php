<?php

namespace App\Models;
//backend\app\Models\User.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    // Relace k oblíbeným receptům
    public function favoriteRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')
            ->withTimestamps();
    }

    // Pomocná metoda pro kontrolu, zda je recept oblíbený
    public function hasFavoriteRecipe($recipeId)
    {
        return $this->favoriteRecipes()->where('recipe_id', $recipeId)->exists();
    }
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
