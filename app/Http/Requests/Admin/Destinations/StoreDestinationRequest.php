<?php

namespace App\Http\Requests\Admin\Destinations;

use App\Models\Destination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Destination::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('destinations', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'hero' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:16384'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('country_code')) {
            $this->merge(['country_code' => strtoupper($this->country_code)]);
        }
    }
}
