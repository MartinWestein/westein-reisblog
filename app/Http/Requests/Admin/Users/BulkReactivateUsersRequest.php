<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Foundation\Http\FormRequest;

class BulkReactivateUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage');
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('ids');

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $this->merge(['ids' => is_array($decoded) ? $decoded : []]);
        }
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Geen gebruikers geselecteerd.',
            'ids.min' => 'Geen gebruikers geselecteerd.',
            'ids.max' => 'Selecteer maximaal 100 gebruikers tegelijk.',
        ];
    }
}
