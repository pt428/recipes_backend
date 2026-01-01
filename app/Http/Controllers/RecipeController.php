<?php
//backend\app\Http\Controllers\RecipeController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkRecipeRequest;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponse;
use Str;

class RecipeController extends Controller
{
    /**
     * Seznam receptů aktuálního uživatele (nebo veřejných).
     */

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12); // Výchozí 10, ale lze změnit
        $perPage = min((int) $perPage, 100); // Maximum 100 receptů na stránku

        $recipes = Recipe::with(['category', 'author', 'tags'])
            ->visibleFor($request->user())
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ApiResponse::success([
            'data' => RecipeResource::collection($recipes->items()),
            'current_page' => $recipes->currentPage(),
            'last_page' => $recipes->lastPage(),
            'per_page' => $recipes->perPage(),
            'total' => $recipes->total(),
        ], 'Recepty byly načteny.');
    }

    /**
     * Vytvoření nového receptu.
     */
    public function store(StoreRecipeRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $recipe = DB::transaction(function () use ($user, $data) {

            $recipe = new Recipe();
            $recipe->fill([
                'title'             => $data['title'],
                'description'       => $data['description'] ?? null,
                'category_id'       => $data['category_id'] ?? null,
                'difficulty'        => $data['difficulty'],
                'prep_time_minutes' => $data['prep_time_minutes'] ?? 0,
                'cook_time_minutes' => $data['cook_time_minutes'] ?? 0,
                'servings'          => $data['servings'],
                'visibility'        => $data['visibility'],
            ]);

            $recipe->user_id = $user->id;
            $recipe->save();

            // ingredience
            if (!empty($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredientData) {
                    $recipe->ingredients()->create([
                        'amount' => $ingredientData['amount'] ?? null,
                        'unit'   => $ingredientData['unit'] ?? null,
                        'name'   => $ingredientData['name'],
                        'note'   => $ingredientData['note'] ?? null,
                    ]);
                }
            }

            // kroky
            if (!empty($data['steps'])) {
                foreach ($data['steps'] as $stepData) {
                    $recipe->steps()->create([
                        'order_index' => $stepData['order_index'],
                        'text'        => $stepData['text'],
                    ]);
                }
            }

            // tagy
            if (!empty($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $recipe->tags()->sync($tagIds);
            }

            return $recipe; // ✅ DŮLEŽITÉ
        });

        // ✅ tady už je $recipe skutečně Model
        $recipe->load(['category', 'author', 'ingredients', 'steps', 'tags']);

        return ApiResponse::success(
            new RecipeResource($recipe),
            'Recept byl vytvořen.'
        );
    }


    /**
     * Zobrazení detailu receptu.
     */
    public function show(Recipe $recipe, Request $request)
    {
        $user = $request->user(); // User | null

        // PRIVATE – pouze autor
        if ($recipe->visibility === 'private') {
            if (!$user || (int) $recipe->user_id !== (int) $user->id) {
                abort(403, 'Nemáte oprávnění zobrazit tento recept.');
            }
        }

        // LINK – může zobrazit pouze autor, ostatní musí použít endpoint /by-link/{token}
        if ($recipe->visibility === 'link') {
            // Pokud je to autor, může vidět recept i přes běžný endpoint
            if (!$user || (int) $recipe->user_id !== (int) $user->id) {
                abort(403, 'Tento recept je dostupný pouze přes sdílený odkaz.');
            }
        }


        // PUBLIC – sem se dostane kdokoliv
        $recipe->load(['category', 'author', 'ingredients', 'steps', 'tags']);

        return ApiResponse::success(
            new RecipeResource($recipe),
            'Recept byl načten.'
        );
    }


    /**
     * Aktualizace receptu.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);
        $user = $request->user();

        if ($recipe->user_id !== $user->id) {
            abort(403, 'Nemáte oprávnění upravovat tento recept.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($recipe, $data) {

            $recipe->fill([
                'title'             => $data['title']             ?? $recipe->title,
                'description'       => $data['description']       ?? $recipe->description,
                'category_id'       => $data['category_id']       ?? $recipe->category_id,
                'difficulty'        => $data['difficulty']        ?? $recipe->difficulty,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? $recipe->prep_time_minutes,
                'cook_time_minutes' => $data['cook_time_minutes'] ?? $recipe->cook_time_minutes,
                'servings'          => $data['servings']          ?? $recipe->servings,
                'serving_type'      => $data['serving_type']      ?? $recipe->serving_type,
                'visibility'        => $data['visibility']        ?? $recipe->visibility,
            ]);
            $recipe->save();

            // Ingredience – pro jednoduchost smažeme a vytvoříme znovu, pokud jsou poslány
            if (array_key_exists('ingredients', $data)) {
                $recipe->ingredients()->delete();

                if (!empty($data['ingredients'])) {
                    foreach ($data['ingredients'] as $ingredientData) {
                        $recipe->ingredients()->create([
                            'amount' => $ingredientData['amount'] ?? null,
                            'unit'   => $ingredientData['unit'] ?? null,
                            'name'   => $ingredientData['name'],
                            'note'   => $ingredientData['note'] ?? null,
                        ]);
                    }
                }
            }

            // Kroky – stejně smažeme a vytvoříme znovu, pokud jsou poslány
            if (array_key_exists('steps', $data)) {
                $recipe->steps()->delete();

                if (!empty($data['steps'])) {
                    foreach ($data['steps'] as $stepData) {
                        $recipe->steps()->create([
                            'order_index' => $stepData['order_index'],
                            'text'        => $stepData['text'],
                        ]);
                    }
                }
            }

            // Tagy
            if (array_key_exists('tags', $data)) {
                if (!empty($data['tags'])) {
                    $tagIds = [];
                    foreach ($data['tags'] as $tagName) {
                        $tag = Tag::firstOrCreate(['name' => $tagName]);
                        $tagIds[] = $tag->id;
                    }
                    $recipe->tags()->sync($tagIds);
                } else {
                    $recipe->tags()->detach();
                }
            }
        });

        $recipe->load(['category', 'author', 'ingredients', 'steps', 'tags']);

        // return new RecipeResource($recipe);
        return ApiResponse::success(
            new RecipeResource($recipe),
            'Recept byl načten.'
        );
    }

    /**
     * Smazání receptu.
     */
    public function destroy(Recipe $recipe, Request $request)
    {
        $this->authorize('delete', $recipe);

        $user = $request->user();

        if ($recipe->user_id !== $user->id) {
            abort(403, 'Nemáte oprávnění smazat tento recept.');
        }

        $recipe->delete();

        // return response()->json([
        //     'message' => 'Recept byl smazán.',
        // ]);
        return ApiResponse::success(
            null,
            'Recept byl smazán.'
        );
    }

    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 12); // Výchozí 12, ale lze změnit
        $perPage = min((int) $perPage, 100); // Maximum 100 receptů na stránku

        $query = Recipe::query()
            ->with(['category', 'tags'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->visibleFor($request->user());

        // fulltext (LIKE – kompatibilní všude)
        if ($search = $request->input('q')) {
            $query->where(function ($q2) use ($search) {
                $q2->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('ingredients', function ($q3) use ($search) {
                        $q3->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // filtr: kategorie
        if ($category = $request->input('category_id')) {
            $query->where('category_id', $category);
        }

        // filtr: obtížnost
        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        // filtr: čas přípravy
        if ($time = $request->input('max_time')) {
            $query->whereRaw(
                '(prep_time_minutes + cook_time_minutes) <= ?',
                [(int) $time]
            );
        }

        // ✅ ZMĚNA: filtr: tagy (recept musí mít ALESPOŇ JEDEN z vybraných tagů - OR logika)
        if ($tags = $request->input('tags')) {
            $tagIds = explode(',', $tags);

            // Filtrujeme pouze platná ID (existující tagy)
            $validTagIds = array_filter($tagIds, function ($id) {
                return is_numeric($id) && $id > 0;
            });

            if (!empty($validTagIds)) {
                $query->whereHas('tags', function ($q2) use ($validTagIds) {
                    $q2->whereIn('tags.id', $validTagIds);
                });
            }
        }

        // řazení – výchozí: nejnovější
        $query->orderBy('created_at', 'desc');

        $recipes = $query->paginate($perPage)->withQueryString();

        return ApiResponse::success([
            'data' => RecipeResource::collection($recipes->items()),
            'current_page' => $recipes->currentPage(),
            'last_page' => $recipes->lastPage(),
            'per_page' => $recipes->perPage(),
            'total' => $recipes->total(),
        ], 'Recepty byly načteny.');
    }

    public function showPublic(string $slug)
    {
        $recipe = Recipe::with(['category', 'author', 'ingredients', 'steps', 'tags'])
            ->where('slug', $slug)
            ->where('visibility', 'public')
            ->firstOrFail();

        // return new RecipeResource($recipe);
        return ApiResponse::success(
            new RecipeResource($recipe),
            'Recept byl načten.'
        );
    }

    public function showByLink(string $token)
    {
        $recipe = Recipe::with(['category', 'author', 'ingredients', 'steps', 'tags'])
            ->where('share_token', $token)
            ->where('visibility', 'link')
            ->firstOrFail();

        // return new RecipeResource($recipe);
        return ApiResponse::success(
            new RecipeResource($recipe),
            'Recept byl načten.'
        );
    }


    public function enableShareLink(Request $request, Recipe $recipe)
    {
        $this->authorize('share', $recipe);
        // jednoduchá autorizace – jen autor receptu
        if ($recipe->user_id !== $request->user()->id) {
            abort(403, 'Nemáte oprávnění sdílet tento recept.');
        }

        if (empty($recipe->share_token)) {
            $recipe->share_token = Str::random(40);
        }

        $recipe->visibility = 'link';
        $recipe->save();

        $publicUrl = config('app.url') . '/shared/' . $recipe->share_token;


        return ApiResponse::success(
            [
                'share_url'   => $publicUrl,
                'share_token' => $recipe->share_token,
            ],
            'Sdílený odkaz byl povolen.'
        );
    }

    public function disableShareLink(Request $request, Recipe $recipe)
    {
        $this->authorize('share', $recipe);
        if ($recipe->user_id !== $request->user()->id) {
            abort(403, 'Nemáte oprávnění upravit sdílení tohoto receptu.');
        }

        $recipe->visibility = 'private';
        $recipe->share_token = null;
        $recipe->save();

        // return response()->json([
        //     'message' => 'Sdílený odkaz byl zrušen.',
        // ]);
        return ApiResponse::success(
            null,
            'Sdílený odkaz byl zrušen.'
        );
    }

    public function storeBulk(StoreBulkRecipeRequest $request)
       {
        $user = $request->user();
        $validated = $request->validated();
        $created = [];
        $errors = [];

        foreach ($validated['recipes'] as $index => $data) {
            try {
                $recipe = DB::transaction(function () use ($user, $data) {

                    $recipe = new Recipe();
                    $recipe->fill([
                        'title'             => $data['title'],
                        'description'       => $data['description'] ?? null,
                        'category_id'       => $data['category_id'] ?? null,
                        'difficulty'        => $data['difficulty'],
                        'prep_time_minutes' => $data['prep_time_minutes'] ?? 0,
                        'cook_time_minutes' => $data['cook_time_minutes'] ?? 0,
                        'servings'          => $data['servings'],
                        'serving_type'      => $data['serving_type'],
                        'visibility'        => $data['visibility'],
                    ]);

                    $recipe->user_id = $user->id;
                    $recipe->save();

                    // ingredience
                    foreach ($data['ingredients'] ?? [] as $ingredient) {
                        $recipe->ingredients()->create([
                            'amount' => $ingredient['amount'] ?? null,
                            'unit'   => $ingredient['unit'] ?? null,
                            'name'   => $ingredient['name'],
                            'note'   => $ingredient['note'] ?? null,
                        ]);
                    }

                    // kroky
                    foreach ($data['steps'] ?? [] as $step) {
                        $recipe->steps()->create([
                            'order_index' => $step['order_index'],
                            'text'        => $step['text'],
                        ]);
                    }

                    // tagy
                    if (!empty($data['tags'])) {
                        $tagIds = collect($data['tags'])
                            ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
                            ->all();

                        $recipe->tags()->sync($tagIds);
                    }

                    return $recipe;
                });

                $recipe->load(['category', 'author', 'ingredients', 'steps', 'tags']);
                $created[] = $recipe;
            } catch (\Throwable $e) {
                $errors[] = [
                    'index' => $index,
                    'title' => $data['title'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ApiResponse::success([
            'created'        => RecipeResource::collection($created),
            'created_count'  => count($created),
            'failed_count'   => count($errors),
            'errors'         => $errors,
        ], 'Hromadné vytvoření receptů dokončeno.');
    }
}
