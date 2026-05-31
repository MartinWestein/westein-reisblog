<?php

namespace App\Http\Requests\Admin\Pages;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pages.manage');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            // slug is read-only bij update (beslissing #10) — niet gevalideerd, niet gemerged
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function publicationData(): array
    {
        if (! $this->boolean('is_published')) {
            return ['published_at' => null];
        }

        return [
            'published_at' => $this->date('published_at') ?? now(),
        ];
    }
}
