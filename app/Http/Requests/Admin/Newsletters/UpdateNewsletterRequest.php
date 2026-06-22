<?php

namespace App\Http\Requests\Admin\Newsletters;

use App\Models\Newsletter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy regelt zowel permission als draft-only status-guard
        return $this->user()->can('update', $this->route('newsletter'));
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:200'],
            'template' => ['required', 'string', Rule::in(Newsletter::TEMPLATES)],
            'body' => ['required', 'string'],
            'header' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
            'remove_header' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_header' => $this->boolean('remove_header'),
        ]);
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Kies een geldige template (announcement, digest of plain).',
        ];
    }
}
