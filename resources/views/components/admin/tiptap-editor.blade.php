@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
])

@php
    // Error-mapping volgt het patroon van <x-admin.field>
    $errorKey = $error ?? $name;
    $errorMessage = $errors->first($errorKey);
    $hasError = filled($errorMessage);

    // Initiële content: oude form-input wint, anders meegegeven value
    $initial = old($name, $value ?? '');

    $editorId = 'tiptap-' . $name . '-' . uniqid();
@endphp

<div class="admin-field">
    @if ($label)
        <label for="{{ $editorId }}" class="admin-field__label">
            {{ $label }}
            @if ($required)
                <span class="admin-field__required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div
        class="tiptap-editor {{ $hasError ? 'is-invalid' : '' }}"
        x-data="tiptapSimple()"
        x-on:beforeunload.window="destroy()"
    >
        <div class="tiptap-editor__toolbar" role="toolbar" aria-label="Tekstopmaak">
            {{-- Tekst-opmaak --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.bold }"
                    @click="toggleBold()" title="Vet (Ctrl+B)" aria-label="Vet">
                    <i class="bi bi-type-bold"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.italic }"
                    @click="toggleItalic()" title="Cursief (Ctrl+I)" aria-label="Cursief">
                    <i class="bi bi-type-italic"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.underline }"
                    @click="toggleUnderline()" title="Onderstrepen (Ctrl+U)" aria-label="Onderstrepen">
                    <i class="bi bi-type-underline"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.strike }"
                    @click="toggleStrike()" title="Doorhalen" aria-label="Doorhalen">
                    <i class="bi bi-type-strikethrough"></i>
                </button>
            </div>

            {{-- Koppen --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.h2 }"
                    @click="setHeading(2)" title="Kop 2" aria-label="Kop 2">H2</button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.h3 }"
                    @click="setHeading(3)" title="Kop 3" aria-label="Kop 3">H3</button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.h4 }"
                    @click="setHeading(4)" title="Kop 4" aria-label="Kop 4">H4</button>
                <button type="button" class="tiptap-editor__btn"
                    @click="setParagraph()" title="Paragraaf" aria-label="Paragraaf">
                    <i class="bi bi-paragraph"></i>
                </button>
            </div>

            {{-- Lijsten + citaat + code --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.bulletList }"
                    @click="toggleBulletList()" title="Lijst" aria-label="Lijst">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.orderedList }"
                    @click="toggleOrderedList()" title="Genummerde lijst" aria-label="Genummerde lijst">
                    <i class="bi bi-list-ol"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.blockquote }"
                    @click="toggleBlockquote()" title="Citaat" aria-label="Citaat">
                    <i class="bi bi-quote"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.code }"
                    @click="toggleCode()" title="Code (inline)" aria-label="Code">
                    <i class="bi bi-code"></i>
                </button>
            </div>

            {{-- Link --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.link }"
                    @click="setLink()" title="Link toevoegen/bewerken" aria-label="Link">
                    <i class="bi bi-link-45deg"></i>
                </button>
            </div>

            {{-- Undo/redo --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    @click="undo()" title="Ongedaan maken (Ctrl+Z)" aria-label="Ongedaan maken">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="redo()" title="Opnieuw (Ctrl+Y)" aria-label="Opnieuw">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        {{-- ProseMirror mount-punt --}}
        <div
            class="tiptap-editor__content"
            id="{{ $editorId }}"
            x-ref="editor"
            @if ($placeholder) data-placeholder="{{ $placeholder }}" @endif
        ></div>

        {{-- Hidden field: bron-van-waarheid voor zowel initial content als form-submission --}}
        <input type="hidden" name="{{ $name }}" x-ref="hidden" value="{{ $initial }}">
    </div>

    @if ($hint && ! $hasError)
        <p class="admin-field__hint">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="admin-field__error" role="alert">{{ $errorMessage }}</p>
    @endif
</div>
