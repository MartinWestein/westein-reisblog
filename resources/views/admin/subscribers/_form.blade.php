@props(['subscriber' => null])

<x-admin.form-section :title="__('Gegevens')">
    <x-admin.field
        name="email"
        :label="__('E-mailadres')"
        :value="old('email', $subscriber?->email)"
        type="email"
        required
        :hint="__('Wordt gebruikt voor de bevestigingsmail en alle toekomstige nieuwsbrieven.')" />

    <x-admin.field
        name="name"
        :label="__('Naam')"
        :value="old('name', $subscriber?->name)"
        :hint="__('Optioneel, gebruikt voor persoonlijke aanhef in mails.')" />
</x-admin.form-section>

@if ($subscriber === null)
    <x-admin.form-section :title="__('Wat gebeurt er na opslaan?')">
        <p class="text-muted small mb-0">
            {{ __('De nieuwe abonnee ontvangt direct een bevestigingsmail. Pas na bevestiging staat de abonnee op actief en ontvangt deze de nieuwsbrief. Dit is AVG-conform.') }}
        </p>
    </x-admin.form-section>
@endif
