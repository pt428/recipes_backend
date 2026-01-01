<?php
// backend\app\Http\Controllers\FavoriteController.php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\RecipeResource;

class FavoriteController extends Controller
{
    /**
     * Vrátí seznam oblíbených receptů aktuálního uživatele
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('per_page', 12);
        $perPage = min((int) $perPage, 100);

        $favorites = $user->favoriteRecipes()
            ->with(['category', 'author', 'tags'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->orderByPivot('created_at', 'desc') // Nejnovější oblíbené nahoře
            ->paginate($perPage);

        return ApiResponse::success([
            'data' => RecipeResource::collection($favorites->items()),
            'current_page' => $favorites->currentPage(),
            'last_page' => $favorites->lastPage(),
            'per_page' => $favorites->perPage(),
            'total' => $favorites->total(),
        ], 'Oblíbené recepty byly načteny.');
    }

    /**
     * Přidá recept do oblíbených
     */
    public function store(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        // Kontrola, zda má uživatel přístup k receptu
        if ($recipe->visibility === 'private' && $recipe->user_id !== $user->id) {
            return ApiResponse::error(
                'Nemáte oprávnění přidat tento recept do oblíbených.',
                403
            );
        }

        // Kontrola, zda už není v oblíbených
        if ($user->hasFavoriteRecipe($recipe->id)) {
            return ApiResponse::error(
                'Recept je již v oblíbených.',
                409
            );
        }

        // Přidání do oblíbených
        $user->favoriteRecipes()->attach($recipe->id);

        return ApiResponse::success(
            [
                'recipe_id' => $recipe->id,
                'is_favorite' => true,
            ],
            'Recept byl přidán do oblíbených.',
            201
        );
    }

    /**
     * Odebere recept z oblíbených
     */
    public function destroy(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        // Kontrola, zda je recept v oblíbených
        if (!$user->hasFavoriteRecipe($recipe->id)) {
            return ApiResponse::error(
                'Recept není v oblíbených.',
                404
            );
        }

        // Odebrání z oblíbených
        $user->favoriteRecipes()->detach($recipe->id);

        return ApiResponse::success(
            [
                'recipe_id' => $recipe->id,
                'is_favorite' => false,
            ],
            'Recept byl odebrán z oblíbených.'
        );
    }

    /**
     * Zkontroluje, zda je recept v oblíbených
     */
    public function check(Request $request, Recipe $recipe)
    {
        $user = $request->user();

        $isFavorite = $user->hasFavoriteRecipe($recipe->id);

        return ApiResponse::success(
            [
                'recipe_id' => $recipe->id,
                'is_favorite' => $isFavorite,
            ],
            'Status oblíbeného receptu byl načten.'
        );
    }
}
