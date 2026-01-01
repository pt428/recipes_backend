<?php
//backend\app\Http\Requests\StoreRecipeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // autorizaci budeme řešit přes auth middleware
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'category_id'       => ['nullable', 'exists:categories,id'],
            'difficulty'        => ['required', 'in:easy,medium,hard'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0'],
            'servings'          => ['required', 'integer', 'min:1'],
            'serving_type' => ['required', 'in:servings,pieces'],
            'visibility'        => ['required', 'in:private,public,link'],

            // ingredience – pole objektů
            'ingredients'                   => ['nullable', 'array'],
            'ingredients.*.amount'          => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit'            => ['nullable', 'string', 'max:20'],
            'ingredients.*.name'            => ['required_with:ingredients', 'string', 'max:255'],
            'ingredients.*.note'            => ['nullable', 'string', 'max:255'],

            // kroky – seřazené podle order_index
            'steps'                         => ['nullable', 'array'],
            'steps.*.order_index'           => ['required_with:steps', 'integer', 'min:1'],
            'steps.*.text'                  => ['required_with:steps', 'string'],

            // tagy – pole stringů nebo ID, zde stringy
            'tags'                          => ['nullable', 'array'],
            'tags.*'                        => ['string', 'max:50'],
        ];
    }
}
