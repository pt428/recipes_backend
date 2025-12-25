<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['sometimes', 'required', 'string', 'max:255'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'category_id'       => ['sometimes', 'nullable', 'exists:categories,id'],
            'difficulty'        => ['sometimes', 'required', 'in:easy,medium,hard'],
            'prep_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'cook_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'servings'          => ['sometimes', 'required', 'integer', 'min:1'],
            'serving_type' => ['sometimes', 'required', 'in:servings,pieces'],
            'visibility'        => ['sometimes', 'required', 'in:private,public,link'],

            'ingredients'                   => ['sometimes', 'nullable', 'array'],
            'ingredients.*.amount'          => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit'            => ['nullable', 'string', 'max:20'],
            'ingredients.*.name'            => ['required_with:ingredients', 'string', 'max:255'],
            'ingredients.*.note'            => ['nullable', 'string', 'max:255'],

            'steps'                         => ['sometimes', 'nullable', 'array'],
            'steps.*.order_index'           => ['required_with:steps', 'integer', 'min:1'],
            'steps.*.text'                  => ['required_with:steps', 'string'],

            'tags'                          => ['sometimes', 'nullable', 'array'],
            'tags.*'                        => ['string', 'max:50'],
        ];
    }
}
