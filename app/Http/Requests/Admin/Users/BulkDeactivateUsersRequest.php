<?php

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeactivateUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.manage');
    }

    /**
     * Payload komt binnen als JSON-string uit hidden form input (name="ids").
     * Voor validation eerst decoden en normaliseren.
     */
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

    /**
     * Guards: F4-U2 (bulk-spiegel: acting-user niet in selectie),
     * F4-U10 (bulk-spiegel: niet alle actieve admins deactiveren).
     * Beide afzonderlijk (F4-U19).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->guardNoSelfInSelection($validator);
            $this->guardNoLastAdminInSelection($validator);
        });
    }

    /**
     * F4-U2 (bulk-spiegel): admin kan zichzelf niet in de bulk-actie opnemen.
     */
    protected function guardNoSelfInSelection(Validator $validator): void
    {
        $ids = (array) $this->input('ids', []);
        $actingUserId = $this->user()->id;

        if (in_array($actingUserId, $ids, true)) {
            $validator->errors()->add(
                'ids',
                __('Je kunt jezelf niet in een bulk-actie opnemen.')
            );
        }
    }

    /**
     * F4-U10 (bulk-spiegel): niet alle actieve admins in één keer deactiveren.
     * Als de selectie alle actueel-actieve admins zou omvatten, blokkeren.
     */
    protected function guardNoLastAdminInSelection(Validator $validator): void
    {
        $ids = (array) $this->input('ids', []);

        if (empty($ids)) {
            return;
        }

        // Actieve admins die IN de selectie zitten
        $activeAdminsInSelection = User::query()
            ->role('admin')
            ->active()
            ->whereIn('id', $ids)
            ->count();

        if ($activeAdminsInSelection === 0) {
            return;
        }

        // Andere actieve admins (buiten selectie)
        $otherActiveAdmins = User::query()
            ->role('admin')
            ->active()
            ->whereNotIn('id', $ids)
            ->count();

        if ($otherActiveAdmins === 0) {
            $validator->errors()->add(
                'ids',
                __('Deze selectie zou alle actieve admins deactiveren. Actie geblokkeerd.')
            );
        }
    }
}
