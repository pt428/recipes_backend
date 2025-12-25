<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transformuje model receptu na JSON strukturu.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'slug'              => $this->slug,
            'image_path'        => $this->image_path,
            'title'             => $this->title,
            'description'       => $this->description,
            'difficulty'        => $this->difficulty,
            'prep_time_minutes' => $this->prep_time_minutes,
            'cook_time_minutes' => $this->cook_time_minutes,
            'servings'          => $this->servings,
            'serving_type' => $this->serving_type,
            'visibility'        => $this->visibility,
            'main_image_url'    => $this->main_image_path
                ? asset('storage/' . $this->main_image_path)
                : null,
            'category'          => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'author'            => $this->whenLoaded('author', function () {
                return [
                    'id'   => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
            'ingredients'       => $this->whenLoaded('ingredients', function () {
                return $this->ingredients->map(function ($ingredient) {
                    return [
                        'id'     => $ingredient->id,
                        'amount' => $ingredient->amount,
                        'unit'   => $ingredient->unit,
                        'name'   => $ingredient->name,
                        'note'   => $ingredient->note,
                    ];
                });
            }),
            'steps'             => $this->whenLoaded('steps', function () {
                return $this->steps->map(function ($step) {
                    return [
                        'id'          => $step->id,
                        'order_index' => $step->order_index,
                        'text'        => $step->text,
                    ];
                });
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                });
            }),
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
            'average_rating' => round($this->ratings()->avg('rating') ?? 0, 2),
            'rating_count'   => $this->ratings()->count(),

        ];
    }
}
