<?php

namespace App\Http\Requests\Admin\Locations;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Location::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('locations', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('country_code')) {
            $this->merge(['country_code' => strtoupper($this->country_code)]);
        }
    }
}
