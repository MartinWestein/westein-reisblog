<?php

namespace App\Http\Requests\Admin\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('content.manage');
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
            // slug is read-only bij update (Vraag 8) — niet gevalideerd, niet gemerged
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Er bestaat al een categorie met deze naam.',
        ];
    }
}
