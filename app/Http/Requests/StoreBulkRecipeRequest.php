<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth řeší middleware
    }

    public function rules(): array
    {
        return [
            'recipes' => ['required', 'array', 'min:1', 'max:50'],

            'recipes.*.title'             => ['required', 'string', 'max:255'],
            'recipes.*.description'       => ['nullable', 'string'],
            'recipes.*.category_id'       => ['nullable', 'exists:categories,id'],
            'recipes.*.difficulty'        => ['required', 'in:easy,medium,hard'],
            'recipes.*.prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'recipes.*.cook_time_minutes' => ['nullable', 'integer', 'min:0'],
            'recipes.*.servings'          => ['required', 'integer', 'min:1'],
            'recipes.*.serving_type'      => ['required', 'in:servings,pieces'],
            'recipes.*.visibility'        => ['required', 'in:public,private,link'],

            // ingredience
            'recipes.*.ingredients'                   => ['nullable', 'array'],
            'recipes.*.ingredients.*.amount'          => ['nullable', 'numeric', 'min:0'],
            'recipes.*.ingredients.*.unit'            => ['nullable', 'string', 'max:20'],
            'recipes.*.ingredients.*.name'            => ['required_with:recipes.*.ingredients', 'string', 'max:255'],
            'recipes.*.ingredients.*.note'            => ['nullable', 'string', 'max:255'],

            // kroky
            'recipes.*.steps'               => ['nullable', 'array'],
            'recipes.*.steps.*.order_index' => ['required_with:recipes.*.steps', 'integer', 'min:1'],
            'recipes.*.steps.*.text'        => ['required_with:recipes.*.steps', 'string'],

            // tagy
            'recipes.*.tags'   => ['nullable', 'array'],
            'recipes.*.tags.*' => ['string', 'max:50'],
        ];
    }
}
