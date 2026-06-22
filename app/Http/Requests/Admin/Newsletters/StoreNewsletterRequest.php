<?php

namespace App\Http\Requests\Admin\Newsletters;

use App\Models\Newsletter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('newsletters.manage');
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:200'],
            'template' => ['required', 'string', Rule::in(Newsletter::TEMPLATES)],
            'body' => ['required', 'string'],
            'header' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Kies een geldige template (announcement, digest of plain).',
        ];
    }
}
