<?php

namespace App\Http\Requests\Admin\Posts;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PostRequest extends FormRequest
{
    /**
     * Slug-regels verschillen per actie:
     * - Store: nullable + uniek (auto-gegenereerd als leeg)
     * - Update: leeg (slug locked, beslissing #10 — niet gevalideerd, niet gemerged)
     *
     * @return array<string, mixed>
     */
    abstract protected function slugRules(): array;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],

            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],

            'status' => ['required', Rule::in($this->allowedStatuses())],
            'published_at' => [
                'nullable',
                'date',
                Rule::when($this->input('status') === 'scheduled', ['required', 'after:now']),
            ],

            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            'featured' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:8192'],
            'remove_featured' => ['nullable', 'boolean'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['boolean'],

            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ], $this->slugRules());
    }

    protected function prepareForValidation(): void
    {
        // Checkbox-conversie (image-upload-component stuurt 'remove_featured')
        $this->merge([
            'remove_featured' => $this->boolean('remove_featured'),
            'is_featured' => $this->boolean('is_featured'),
        ]);

        // Tag-pills komen binnen als één komma-gescheiden string → normaliseer naar array.
        // (Eén hidden veld is robuuster dan dynamische naam-arrays; syncTagsByName lowercaset zelf.)
        if (is_string($this->input('tags'))) {
            $tags = collect(explode(',', $this->input('tags')))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->values()
                ->all();

            $this->merge(['tags' => $tags]);
        }
    }

    /**
     * Toegestane statussen afhankelijk van publish-recht.
     * 'scheduled' en 'published' maken een post (nu of straks) publiek → posts.publish vereist.
     * 'draft' en 'archived' maken niets publiek → vrij voor iedereen met create/update.
     *
     * @return array<int, string>
     */
    protected function allowedStatuses(): array
    {
        return $this->user()->can('posts.publish')
            ? ['draft', 'scheduled', 'published', 'archived']
            : ['draft', 'archived'];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $destinationId = $this->input('destination_id');
            $locationId = $this->input('location_id');

            // §3.4 — een locatie moet binnen de gekozen bestemming vallen
            if ($locationId) {
                if (! $destinationId) {
                    $validator->errors()->add(
                        'destination_id',
                        'Kies ook een bestemming wanneer je een locatie selecteert.'
                    );
                } else {
                    $location = Location::find($locationId);
                    if ($location && (int) $location->destination_id !== (int) $destinationId) {
                        $validator->errors()->add(
                            'location_id',
                            'Deze locatie hoort niet bij de gekozen bestemming.'
                        );
                    }
                }
            }

            // §3.4 — bestemming verplicht, tenzij de post de Tips-categorie heeft (permissief)
            if (! $destinationId && ! $this->hasTipsCategory()) {
                $validator->errors()->add(
                    'destination_id',
                    'Kies een bestemming, of geef de post de categorie Tips.'
                );
            }
        });
    }

    /**
     * Heeft de inzending de algemene Tips-categorie geselecteerd?
     */
    protected function hasTipsCategory(): bool
    {
        $categoryIds = collect($this->input('categories', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        if (empty($categoryIds)) {
            return false;
        }

        return Category::whereIn('id', $categoryIds)
            ->where('slug', config('westein.general_tips_category_slug', 'tips'))
            ->exists();
    }

    /**
     * Normaliseer published_at op basis van de gekozen status.
     * - draft     → null
     * - scheduled → gekozen (toekomstige) datum
     * - published → gekozen datum óf nu (backdaten toegestaan voor imports)
     * - archived  → ongemoeid (behoudt een eerdere publicatiedatum)
     *
     * @return array<string, mixed>
     */
    public function publicationData(): array
    {
        return match ($this->input('status')) {
            'draft' => ['published_at' => null],
            'scheduled' => ['published_at' => $this->date('published_at')],
            'published' => ['published_at' => $this->date('published_at') ?? now()],
            default => [], // 'archived'
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug mag alleen kleine letters, cijfers en streepjes bevatten.',
            'slug.unique' => 'Er bestaat al een post met deze slug.',
            'status.in' => 'Je hebt geen rechten om posts te publiceren of in te plannen.',
            'published_at.required' => 'Kies een publicatiedatum voor een geplande post.',
            'published_at.after' => 'Een geplande post moet een publicatiedatum in de toekomst hebben.',
        ];
    }
}
