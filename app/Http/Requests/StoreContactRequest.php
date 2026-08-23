<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Open voor iedereen (F5-113); spam-afweer via honeypot + throttle op de route.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'], // geen dns (test-domein-landmine)
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vul je naam in.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'subject.required' => 'Vul een onderwerp in.',
            'message.required' => 'Schrijf eerst een bericht.',
            'message.min' => 'Je bericht is wel erg kort.',
        ];
    }
}
