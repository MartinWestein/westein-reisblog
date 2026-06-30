<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.browse') ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:media,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Selecteer minimaal één item.',
            'ids.max' => 'Maximaal 100 items per actie.',
        ];
    }
}
