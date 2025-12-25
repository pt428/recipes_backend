<?php
//backend\routes\api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TagController;
// veřejné endpointy  slug= nazev receptu bez diakritiky a mezer 


Route::get('/recipes', [RecipeController::class, 'index']) //funguje
    ->middleware('optional.auth');
Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])->middleware('optional.auth');; //funguje


Route::get('/recipes/search', [RecipeController::class, 'search']);

Route::get('/recipes/public/{slug}', [RecipeController::class, 'showPublic']); //funguje
Route::get('/recipes/by-link/{token}', [RecipeController::class, 'showByLink']);
Route::get('/recipes/{recipe}/rating', [RatingController::class, 'show']);
Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']); //funguje
Route::post('/register', [AuthController::class, 'register']); //funguje
Route::post('/login',    [AuthController::class, 'login']); //funguje
 Route::get('/tags', [TagController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); //funguje
    Route::get('/user',    [AuthController::class, 'me']); //funguje
    Route::delete('/user', [AuthController::class, 'destroy']); //funguje
    Route::put('/user', [AuthController::class, 'update']); //funguje

    //recepty
    Route::post('/recipes', [RecipeController::class, 'store']); //funguje
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']); //funguje
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']); //funguje
    //recepty-obrazky
    Route::post('/recipes/{recipe}/image', [RecipeImageController::class, 'store']); //funguje
    Route::delete('/recipes/{recipe}/image', [RecipeImageController::class, 'destroy']); //funguje
    //recepty-sdileny link
    Route::post('/recipes/{recipe}/share', [RecipeController::class, 'enableShareLink']); //funguje
    Route::delete('/recipes/{recipe}/share', [RecipeController::class, 'disableShareLink']); //funguje


    // komentáře  
    Route::post('/recipes/{recipe}/comments', [CommentController::class, 'store']); //funguje
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']); //funguje

    // hodnocení
    Route::post('/recipes/{recipe}/rating', [RatingController::class, 'store']); //funguje
    Route::delete('/recipes/{recipe}/rating', [RatingController::class, 'destroy']);

   
});
