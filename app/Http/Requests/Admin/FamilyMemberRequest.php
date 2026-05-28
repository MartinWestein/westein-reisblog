<?php

namespace App\Http\Requests\Admin;

use App\Models\FamilyMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('family_member');

        return $member instanceof FamilyMember
            ? $this->user()->can('update', $member)
            : $this->user()->can('create', FamilyMember::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:80'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id'),
            ],
            'portrait' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=300,min_height=300',
            ],
            'remove_portrait' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'naam',
            'role' => 'rol',
            'bio' => 'biografie',
            'order' => 'sortering',
            'user_id' => 'gekoppelde gebruiker',
            'portrait' => 'portretfoto',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->input('user_id') === '' ? null : $this->input('user_id'),
            'order' => $this->input('order') === '' || $this->input('order') === null
                ? 0
                : (int) $this->input('order'),
        ]);
    }
}
