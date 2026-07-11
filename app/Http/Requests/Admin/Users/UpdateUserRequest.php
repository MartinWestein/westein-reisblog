<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        /** @var User $editedUser */
        $editedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($editedUser->id),
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
     * Guards: F4-U2 (geen zelf-lock op admin-rol) + F4-U10 (geen laatste-admin-verlies).
     * Beide checks worden afzonderlijk uitgevoerd (F4-U19).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->guardNoSelfLock($validator);
            $this->guardNoLastAdminRoleLoss($validator);
        });
    }

    /**
     * F4-U2 (guard 1): admin kan eigen admin-rol niet verwijderen.
     */
    protected function guardNoSelfLock(Validator $validator): void
    {
        /** @var User $editedUser */
        $editedUser = $this->route('user');
        $actingUser = $this->user();

        // Alleen relevant als admin zichzelf bewerkt
        if ($editedUser->id !== $actingUser->id) {
            return;
        }

        // Alleen relevant als de acting-user actueel admin is
        if (! $actingUser->hasRole('admin')) {
            return;
        }

        // Als 'admin' niet in de nieuwe rollen-set zit, blokkeren
        $newRoles = (array) $this->input('roles', []);
        if (! in_array('admin', $newRoles, true)) {
            $validator->errors()->add(
                'roles',
                __('Je kunt je eigen admin-rol niet verwijderen.')
            );
        }
    }

    /**
     * F4-U10 (guard 2): laatste actieve admin mag z'n admin-rol niet verliezen.
     */
    protected function guardNoLastAdminRoleLoss(Validator $validator): void
    {
        /** @var User $editedUser */
        $editedUser = $this->route('user');

        // Alleen relevant als de bewerkte user momenteel admin is
        if (! $editedUser->hasRole('admin')) {
            return;
        }

        // Alleen relevant als de bewerkte user actief is (deactivated telt niet als admin)
        if ($editedUser->deactivated_at !== null) {
            return;
        }

        // Alleen relevant als admin uit de nieuwe rollen-set wordt gehaald
        $newRoles = (array) $this->input('roles', []);
        if (in_array('admin', $newRoles, true)) {
            return;
        }

        // Tel andere actieve admins (excl. de user die we bewerken)
        $otherActiveAdmins = User::query()
            ->role('admin')
            ->active()
            ->where('id', '!=', $editedUser->id)
            ->count();

        if ($otherActiveAdmins === 0) {
            $validator->errors()->add(
                'roles',
                __('Dit is de laatste actieve admin. De admin-rol kan niet verwijderd worden.')
            );
        }
    }

    /**
     * Rollen die geselecteerd mogen worden (whitelist tegen tampering).
     */
    protected function availableRoleNames(): array
    {
        return Role::query()->pluck('name')->all();
    }
}
