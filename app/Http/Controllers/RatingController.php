<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Rating;
use App\Helpers\ApiResponse;
class RatingController extends Controller
{
    public function store(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $rating = Rating::updateOrCreate(
            [
                'recipe_id' => $recipe->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
            ]
        );

        // return response()->json($rating, 201);
        return ApiResponse::success(
            $rating,
            'Hodnocení bylo vytvořeno.',
            201
        );
    }
    public function show(Request $request, Recipe $recipe)
    {
        $rating = $recipe->ratings()
            ->where('user_id', $request->user()->id)
            ->first();

        // return response()->json([
        //     'rating' => $rating?->rating,
        // ]);
        return ApiResponse::success(
            ['rating' => $rating?->rating],
            'Aktuální hodnocení bylo načteno.'
        );
    }
    public function destroy(Request $request, Recipe $recipe)
    {
        $rating = $recipe->ratings()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $rating->delete();

        // return response()->json([
        //     'message' => 'Hodnocení bylo odstraněno.'
        // ]);
        return ApiResponse::success(
            null,
            'Hodnocení bylo odstraněno.'
        );
    }
}
