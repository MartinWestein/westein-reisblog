<?php

namespace App\Http\Requests\Admin\Routes;

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class RouteRequest extends FormRequest
{
    /**
     * Subclass bepaalt of/hoe slug gevalideerd wordt.
     * Store: nullable + alpha_dash + unique. Update: leeg (slug locked).
     */
    abstract protected function slugRules(): array;

    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            ...$this->slugRules(),
            'description' => ['nullable', 'string', 'max:65535'],
            'travel_date' => ['nullable', 'date'],

            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],

            'waypoints' => ['nullable', 'array'],
            'waypoints.*.location_id' => ['required', 'integer', 'exists:locations,id'],
            'waypoints.*.notes' => ['nullable', 'string', 'max:1000'],

            'hero' => [
                'nullable', 'image', 'mimes:jpeg,png,webp', 'max:16384',
                'dimensions:min_width=600,min_height=338',
            ],
            'remove_hero' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Waypoints komen binnen als JSON-string uit het hidden field.
        // Bij submit decoderen naar array zodat normale array-validatie werkt.
        $waypoints = $this->input('waypoints');
        if (is_string($waypoints)) {
            $decoded = json_decode($waypoints, true);
            $this->merge(['waypoints' => is_array($decoded) ? $decoded : []]);
        }

        // is_published is een form-helper (checkbox/toggle), niet altijd in payload.
        $this->merge(['is_published' => $this->boolean('is_published')]);

        // Idem is_featured - missing key betekent uitgevinkt.
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $destinationId = $this->input('destination_id');
            $waypoints = $this->input('waypoints', []);

            if (! $destinationId || empty($waypoints) || ! is_array($waypoints)) {
                return;
            }

            $locationIds = collect($waypoints)
                ->pluck('location_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($locationIds)) {
                return;
            }

            $offendingCount = Location::whereIn('id', $locationIds)
                ->where('destination_id', '!=', $destinationId)
                ->count();

            if ($offendingCount > 0) {
                $validator->errors()->add(
                    'waypoints',
                    'Alle waypoints moeten binnen de gekozen bestemming liggen.'
                );
            }
        });
    }

    /**
     * Berekent de publicatiegegevens. Eerste publicatie zet published_at op nu
     * (of op een door admin gekozen datum). Bij unpublish blijft published_at staan
     * — historie behouden zodat we later in stats kunnen zien wanneer iets ooit live ging.
     *
     * @return array{is_published: bool, published_at?: Carbon|null}
     */
    public function publicationData(): array
    {
        $isPublished = $this->boolean('is_published');

        if (! $isPublished) {
            return ['is_published' => false];
        }

        $publishedAt = $this->input('published_at')
            ? Carbon::parse($this->input('published_at'))
            : now();

        return [
            'is_published' => true,
            'published_at' => $publishedAt,
        ];
    }

    public function messages(): array
    {
        return [
            'destination_id.required' => 'Kies een bestemming voor deze reisroute.',
            'destination_id.exists' => 'De gekozen bestemming bestaat niet.',
            'name.required' => 'Geef de reisroute een naam.',
            'slug.unique' => 'Deze URL-slug is al in gebruik.',
            'slug.alpha_dash' => 'De slug mag alleen letters, cijfers, koppeltekens en underscores bevatten.',
            'waypoints.*.location_id.required' => 'Elk waypoint moet een locatie hebben.',
            'waypoints.*.location_id.exists' => 'De gekozen locatie bestaat niet.',
            'waypoints.*.notes.max' => 'De notitie mag maximaal 1000 tekens bevatten.',
            'hero.image' => 'De hero-afbeelding moet een afbeelding zijn.',
            'hero.mimes' => 'De hero-afbeelding moet JPEG, PNG of WebP zijn.',
            'hero.max' => 'De hero-afbeelding mag maximaal 16 MB zijn.',
            'hero.dimensions' => 'De hero-afbeelding moet minimaal 600×338 pixels zijn.',
        ];
    }
}
