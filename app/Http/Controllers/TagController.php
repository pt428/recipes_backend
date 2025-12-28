<?php
// backend/app/Http/Controllers/TagController.php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class TagController extends Controller
{
    /**
     * Vrátí seznam tagů, které mají alespoň jeden viditelný recept
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Načteme tagy, které mají alespoň jeden viditelný recept
        $tags = Tag::whereHas('recipes', function ($query) use ($user) {
            // Použijeme stejnou logiku visibility jako u receptů
            $query->visibleFor($user);
        })
        ->withCount(['recipes' => function ($query) use ($user) {
            $query->visibleFor($user);
        }])
        ->orderBy('name')
        ->get();

        return ApiResponse::success(
            $tags,
            'Tagy byly načteny.'
        );
    }

    /**
     * Alternativní metoda - vrátí všechny tagy (pokud chcete zobrazit i prázdné)
     */
    public function all(Request $request)
    {
        $user = $request->user();

        $tags = Tag::withCount(['recipes' => function ($query) use ($user) {
            $query->visibleFor($user);
        }])
        ->orderBy('name')
        ->get();

        return ApiResponse::success(
            $tags,
            'Všechny tagy byly načteny.'
        );
    }
}