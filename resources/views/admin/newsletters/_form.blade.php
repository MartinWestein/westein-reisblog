{{--
    Gedeelde form-partial voor Newsletters create + edit.
    Verwacht: $newsletter (Newsletter of null), $action (URL), $method ('POST' / 'PUT').
--}}
@php
    $isEdit = ! is_null($newsletter);

    $values = [
        'subject' => old('subject', $newsletter?->subject ?? ''),
        'body' => old('body', $newsletter?->body ?? ''),
        'template' => old('template', $newsletter?->template ?? \App\Models\Newsletter::TEMPLATE_PLAIN),
    ];

    $headerUrl = $isEdit ? $newsletter->getFirstMediaUrl('header', 'medium') : '';

    $templateLabels = [
        \App\Models\Newsletter::TEMPLATE_ANNOUNCEMENT => __('Aankondiging'),
        \App\Models\Newsletter::TEMPLATE_DIGEST => __('Verzameling'),
        \App\Models\Newsletter::TEMPLATE_PLAIN => __('Eenvoudig'),
    ];

    $activeSubscriberCount = \App\Models\Subscriber::active()->count();
@endphp

<x-admin.form-layout :action="$action" :method="$method" enctype="multipart/form-data">
    {{-- ===== HOOFDCONTENT LINKS ===== --}}
    <x-slot:main>
        <x-admin.form-section title="Inhoud">
            <x-admin.field
                name="subject"
                label="Onderwerp"
                :value="$values['subject']"
                required
                hint="Verschijnt als e-mail-subject en als titel in de mail zelf."
            />

            <div class="admin-field">
                <label class="admin-field__label" for="template">{{ __('Sjabloon') }}
                    <span class="admin-field__required" aria-hidden="true">*</span>
                </label>
                <select
                    name="template"
                    id="template"
                    class="form-select @error('template') is-invalid @enderror"
                >
                    @foreach ($templateLabels as $value => $label)
                        <option value="{{ $value }}" @selected($values['template'] === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('template')
                    <p class="admin-field__error" role="alert">{{ $message }}</p>
                @enderror
                <p class="admin-field__hint">{{ __('Bepaalt de lay-out van de verzonden e-mail.') }}</p>
            </div>

            <x-admin.tiptap-editor
                name="body"
                label="Bericht"
                :value="$values['body']"
                placeholder="{{ __('Schrijf hier het bericht…') }}"
                required
            />
        </x-admin.form-section>
    </x-slot:main>

    {{-- ===== METADATA RECHTS ===== --}}
    <x-slot:side>
        <x-admin.form-section title="Hero-afbeelding">
            <x-admin.image-upload
                name="header"
                shape="square"
                :current-url="$headerUrl"
                :max-mb="8"
                :min-width="600"
                :min-height="400"
                :remove-label="__('Hero-afbeelding verwijderen bij opslaan')"
                hint="{{ __('Optioneel. Verschijnt bovenaan de e-mail.') }}"
            />
        </x-admin.form-section>

        <x-admin.form-section title="Ontvangers">
            <p class="admin-field__hint mb-0">
                {{ trans_choice(
                    '{0} Er zijn nog geen actieve abonnees.|{1} Deze nieuwsbrief gaat naar :count actieve abonnee.|[2,*] Deze nieuwsbrief gaat naar :count actieve abonnees.',
                    $activeSubscriberCount,
                    ['count' => $activeSubscriberCount]
                ) }}
            </p>
            @if ($activeSubscriberCount === 0)
                <p class="text-warning small mt-2 mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ __('Verzenden lukt pas wanneer er minstens één actieve abonnee is.') }}
                </p>
            @endif
        </x-admin.form-section>

        @if ($isEdit)
            <x-admin.form-section title="Metadata">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">{{ __('Status') }}</dt>
                    <dd class="col-7">
                        @php
                            $badgeClass = match ($newsletter->status) {
                                'draft' => 'bg-secondary',
                                'sending' => 'bg-info text-dark',
                                'sent' => 'bg-success',
                                default => 'bg-light text-dark',
                            };
                            $badgeLabel = match ($newsletter->status) {
                                'draft' => __('Concept'),
                                'sending' => __('Wordt verzonden'),
                                'sent' => __('Verzonden'),
                                default => $newsletter->status,
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </dd>

                    <dt class="col-5 text-muted">{{ __('Auteur') }}</dt>
                    <dd class="col-7">{{ $newsletter->author?->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('Aangemaakt') }}</dt>
                    <dd class="col-7">{{ $newsletter->created_at->isoFormat('D MMM YYYY HH:mm') }}</dd>

                    @if ($newsletter->sent_at)
                        <dt class="col-5 text-muted">{{ __('Verzonden op') }}</dt>
                        <dd class="col-7">{{ $newsletter->sent_at->isoFormat('D MMM YYYY HH:mm') }}</dd>
                    @endif
                </dl>
            </x-admin.form-section>
        @endif
    </x-slot:side>

    {{-- ===== ACTIE-KNOPPEN ===== --}}
    <x-slot:actions>
        <a href="{{ route('admin.newsletters.index') }}" class="btn btn-link text-muted">
            {{ __('Annuleren') }}
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i>
            {{ $isEdit ? __('Wijzigingen opslaan') : __('Concept aanmaken') }}
        </button>
    </x-slot:actions>
</x-admin.form-layout>
