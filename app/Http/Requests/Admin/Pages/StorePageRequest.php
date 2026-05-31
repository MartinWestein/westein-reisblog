<?php

namespace App\Http\Requests\Admin\Pages;

use App\Rules\NotReservedSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pages.manage');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages', 'slug'),
                new NotReservedSlug,
            ],
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
        // Checkbox-conversie: HTML-checkboxen sturen niks bij 'uit', '1' bij 'aan'
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug mag alleen kleine letters, cijfers en streepjes bevatten.',
            'slug.unique' => 'Er bestaat al een pagina met deze slug.',
        ];
    }

    /**
     * Normaliseer published_at op basis van de toggle:
     * - toggle uit → published_at = null (concept)
     * - toggle aan + geen datum → published_at = now() (direct)
     * - toggle aan + datum → published_at = die datum (mogelijk gepland)
     */
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
