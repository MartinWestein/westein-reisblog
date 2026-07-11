<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DeactivateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'De reden mag maximaal 500 tekens bevatten.',
        ];
    }

    /**
     * Guards: F4-U2 (geen zelf-lock via deactivate) + F4-U10 (geen laatste-admin-verlies).
     * Beide checks afzonderlijk (F4-U19).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->guardNoSelfDeactivate($validator);
            $this->guardNoLastAdminDeactivate($validator);
        });
    }

    /**
     * F4-U2 (guard 1): admin kan zichzelf niet deactiveren (zelf-lock via deactivate).
     */
    protected function guardNoSelfDeactivate(Validator $validator): void
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user');
        $actingUser = $this->user();

        if ($targetUser->id === $actingUser->id) {
            $validator->errors()->add(
                'reason',
                __('Je kunt jezelf niet deactiveren.')
            );
        }
    }

    /**
     * F4-U10 (guard 2): laatste actieve admin mag niet gedeactiveerd worden.
     */
    protected function guardNoLastAdminDeactivate(Validator $validator): void
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user');

        // Alleen relevant als de target user momenteel admin is
        if (! $targetUser->hasRole('admin')) {
            return;
        }

        // Alleen relevant als de target user actief is (anders al gedeactiveerd)
        if ($targetUser->deactivated_at !== null) {
            return;
        }

        // Tel andere actieve admins (excl. de target user)
        $otherActiveAdmins = User::query()
            ->role('admin')
            ->active()
            ->where('id', '!=', $targetUser->id)
            ->count();

        if ($otherActiveAdmins === 0) {
            $validator->errors()->add(
                'reason',
                __('Dit is de laatste actieve admin. Deactivatie is niet toegestaan.')
            );
        }
    }
}
