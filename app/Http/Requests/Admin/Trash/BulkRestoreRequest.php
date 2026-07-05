<?php

namespace App\Http\Requests\Admin\Trash;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkRestoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('trash.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('items');

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $this->merge(['items' => is_array($decoded) ? $decoded : []]);
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.type' => ['required', 'string', Rule::in(['post', 'destination', 'location', 'route', 'page'])],
            'items.*.id' => ['required', 'integer', 'min:1'],
        ];
    }
}
