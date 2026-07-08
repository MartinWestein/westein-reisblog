<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($this->availableRoleNames())],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Er bestaat al een gebruiker met dit e-mailadres.',
            'roles.*.in' => 'Ongeldige rol geselecteerd.',
        ];
    }

    /**
     * Rollen die geselecteerd mogen worden (whitelist tegen tampering).
     */
    protected function availableRoleNames(): array
    {
        return Role::query()
            ->pluck('name')
            ->all();
    }
}
