<?php

namespace App\Http\Requests\Admin\Destinations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('destination'));
    }

    public function rules(): array
    {
        // Slug bewust weggelaten — read-only bij update (conventie #8, Pages-patroon).
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'hero' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:16384'],
            'remove_hero' => ['nullable', 'boolean'],
            'is_featured' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
        ]);

        if ($this->filled('country_code')) {
            $this->merge(['country_code' => strtoupper($this->country_code)]);
        }
    }
}
