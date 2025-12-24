<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Comment;
use App\Helpers\ApiResponse;
class CommentController extends Controller
{
    public function store(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $recipe->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        // return response()->json($comment, 201);
        return ApiResponse::success($comment, 'Komentář byl vytvořen.', 201);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        // return response()->json(['message' => 'Komentář byl smazán.']);
        return ApiResponse::success(null, 'Komentář byl smazán.');
    }
    public function index(Recipe $recipe)
    {
        $comments = $recipe->comments()
            ->with('user:id,name') // předejdeme N+1
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // return response()->json($comments);
        return ApiResponse::success($comments);
    }
}
