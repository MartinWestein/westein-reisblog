<?php

namespace App\Http\Requests\Admin\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('content.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')],
            'slug' => ['nullable', 'string', 'max:140', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string', 'max:500'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug mag alleen kleine letters, cijfers en streepjes bevatten.',
            'name.unique' => 'Er bestaat al een categorie met deze naam.',
            'slug.unique' => 'Er bestaat al een categorie met deze slug.',
        ];
    }
}
