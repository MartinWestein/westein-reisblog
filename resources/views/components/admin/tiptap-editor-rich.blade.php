@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'post' => null,
])

@php
    // Error-mapping volgt het patroon van <x-admin.field>
    $errorKey = $error ?? $name;
    $errorMessage = $errors->first($errorKey);
    $hasError = filled($errorMessage);

    // Initiële content: oude form-input wint, anders meegegeven value
    $initial = old($name, $value ?? '');

    $editorId = 'tiptap-rich-' . $name . '-' . uniqid();
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
        class="tiptap-editor tiptap-editor--rich {{ $hasError ? 'is-invalid' : '' }}"
        x-data="tiptapRich()"
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

            {{-- Lijsten + citaat + horizontale lijn --}}
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
                    @click="insertHorizontalRule()" title="Scheidingslijn" aria-label="Scheidingslijn">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>

            {{-- Code (inline + block) --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.code }"
                    @click="toggleCode()" title="Code (inline)" aria-label="Code inline">
                    <i class="bi bi-code"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.codeBlock }"
                    @click="toggleCodeBlock()" title="Codeblok" aria-label="Codeblok">
                    <i class="bi bi-code-square"></i>
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

            {{-- Afbeelding (stap 4.6) --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    @click="openImagePicker()"
                    title="Afbeelding invoegen" aria-label="Afbeelding invoegen">
                    <i class="bi bi-image"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.imageAlign === 'left' }"
                    :disabled="!state.image"
                    @click="setImageAlign('left')"
                    title="Links uitlijnen" aria-label="Links uitlijnen">
                    <i class="bi bi-align-start"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.imageAlign === 'center' }"
                    :disabled="!state.image"
                    @click="setImageAlign('center')"
                    title="Centreren" aria-label="Centreren">
                    <i class="bi bi-align-center"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.imageAlign === 'right' }"
                    :disabled="!state.image"
                    @click="setImageAlign('right')"
                    title="Rechts uitlijnen" aria-label="Rechts uitlijnen">
                    <i class="bi bi-align-end"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    :class="{ 'is-active': state.imageAlign === 'full' }"
                    :disabled="!state.image"
                    @click="setImageAlign('full')"
                    title="Volle breedte" aria-label="Volle breedte">
                    <i class="bi bi-arrows"></i>
                </button>
            </div>
            {{-- Tabel-commando's --}}
            <div class="tiptap-editor__group">
                <button type="button" class="tiptap-editor__btn"
                    @click="insertTable()" title="Tabel invoegen (3×3)" aria-label="Tabel invoegen">
                    <i class="bi bi-table"></i>
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="addColumnAfter()" :disabled="!state.table"
                    title="Kolom toevoegen" aria-label="Kolom toevoegen">
                    <i class="bi bi-layout-three-columns"></i>+
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="addRowAfter()" :disabled="!state.table"
                    title="Rij toevoegen" aria-label="Rij toevoegen">
                    <i class="bi bi-layout-split"></i>+
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="deleteColumn()" :disabled="!state.table"
                    title="Kolom verwijderen" aria-label="Kolom verwijderen">
                    <i class="bi bi-layout-three-columns"></i>−
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="deleteRow()" :disabled="!state.table"
                    title="Rij verwijderen" aria-label="Rij verwijderen">
                    <i class="bi bi-layout-split"></i>−
                </button>
                <button type="button" class="tiptap-editor__btn"
                    @click="deleteTable()" :disabled="!state.table"
                    title="Tabel verwijderen" aria-label="Tabel verwijderen">
                    <i class="bi bi-trash"></i>
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
{{-- Modal voor stap 4.6: rendert één keer per editor-instance, gekoppeld via Alpine.store --}}
<x-admin.image-picker-modal :post="$post" />
