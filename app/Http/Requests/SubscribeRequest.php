<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Open voor iedereen; spam-afweer via honeypot + throttle op de route.
    }

    public function rules(): array
    {
        // Bewust GEEN Rule::unique — publiek weigeren op duplicaat lekt e-mail-enumeratie.
        // SubscribeAction handelt een bestaand adres idempotent af (F4-17).
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:190'], // geen dns (test-domein-landmine, F4-18)
            'name' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
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
// EOF
