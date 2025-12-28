<?php
//backend\app\Http\Controllers\CategoryController.php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Vrátí seznam všech kategorií
     */
    public function index()
    {
        $categories = Category::withCount('recipes')
            ->orderBy('name', 'asc')
            ->get();

        return ApiResponse::success(
            $categories,
            'Kategorie byly načteny.'
        );
    }

    /**
     * Vytvoření nové kategorie
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Název kategorie je povinný.',
            'name.unique' => 'Kategorie s tímto názvem již existuje.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validace selhala.',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);

        return ApiResponse::success(
            $category,
            'Kategorie byla vytvořena.',
            201
        );
    }

    /**
     * Aktualizace kategorie
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ], [
            'name.required' => 'Název kategorie je povinný.',
            'name.unique' => 'Kategorie s tímto názvem již existuje.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validace selhala.',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return ApiResponse::success(
            $category,
            'Kategorie byla aktualizována.'
        );
    }

    /**
     * Smazání kategorie
     */
    public function destroy(Category $category)
    {
        // Zkontrolujeme, jestli kategorie má nějaké recepty
        $recipesCount = $category->recipes()->count();

        if ($recipesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Kategorii nelze smazat, protože obsahuje {$recipesCount} receptů. Nejprve přesuňte recepty do jiné kategorie."
            ], 400);
        }

        $category->delete();

        return ApiResponse::success(
            null,
            'Kategorie byla smazána.'
        );
    }
}
