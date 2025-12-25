<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $tags
        ]);
    }
}
