<?php

namespace App\Http\Requests\Admin\Subscribers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('subscriber')) ?? false;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',     // bewust geen 'dns' — bevestigingsmail-bounce vangt domeinfouten af
                'max:255',
                Rule::unique('subscribers', 'email')->ignore($this->route('subscriber')),
            ],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : null,
        ]);
    }
}
