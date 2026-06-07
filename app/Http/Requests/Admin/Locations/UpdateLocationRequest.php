<?php

namespace App\Http\Requests\Admin\Locations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('location'));
    }

    public function rules(): array
    {
        // Slug bewust weggelaten — read-only bij update (Pages-patroon, leerpunt #8).
        return [
            'name' => ['required', 'string', 'max:255'],
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
