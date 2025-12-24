<?php
//backend\app\Http\Controllers\RecipeController.php
namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;


class RecipeImageController extends Controller
{

    public function store(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2 MB
            ],
        ]);

        // Smazání starého obrázku, pokud existuje
        if ($recipe->image_path && Storage::disk('public')->exists($recipe->image_path)) {
            Storage::disk('public')->delete($recipe->image_path);
        }

        // Uložení nového obrázku
        $path = $request->file('image')->store('recipes', 'public');

        // Uložení do DB
        $recipe->update([
            'image_path' => $path,
        ]);

        // return response()->json([
        //     'message' => 'Obrázek byl nahrán.',
        //     'image_url' => Storage::url($path),
        // ]);
        return ApiResponse::success(
            ['image_url' => Storage::url($path)],
            'Obrázek byl nahrán.'
        );
    }
 

    public function destroy(Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        try {
            if (!$recipe->image_path) {
                return response()->json([
                    'message' => 'Recept nemá přiřazený obrázek.',
                ], 404);
            }

            // Pokus o smazání souboru
            if (!Storage::disk('public')->delete($recipe->image_path)) {
                return response()->json([
                    'message' => 'Obrázek se nepodařilo smazat ze storage.',
                ], 500);
            }

            // Aktualizace DB
            $recipe->update([
                'image_path' => null,
            ]);

            return response()->json([
                'message' => 'Obrázek byl úspěšně odstraněn.',
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Došlo k neočekávané chybě.',
            ], 500);
        }
    }

    // public function destroy(Recipe $recipe)
    // {
    //     $this->authorize('update', $recipe);

    //     if ($recipe->image_path) {
    //         Storage::disk('public')->delete($recipe->image_path);

    //         $recipe->update([
    //             'image_path' => null,
    //         ]);
    //     }

    //     return response()->noContent();
    // }
}
