@props(['post', 'parent' => null])

{{-- Herbruikbaar reactieformulier: top-level (parent = null) én reply (parent gezet).
     Honeypot-patroon geleend van auth/register (F5-90). --}}
<form method="POST" action="{{ route('comments.store', $post) }}" class="comment-form">
    @csrf
    @honeypot
    @if ($parent)
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
    @endif

    <label for="comment-body-{{ $parent?->id ?? 'root' }}" class="visually-hidden">Reactie</label>
    <textarea
        id="comment-body-{{ $parent?->id ?? 'root' }}"
        name="body"
        rows="{{ $parent ? 2 : 4 }}"
        maxlength="2000"
        required
        placeholder="{{ $parent ? 'Schrijf een antwoord…' : 'Schrijf je reactie…' }}"
        class="form-control @error('body', 'comment') is-invalid @enderror">{{ old('body') }}</textarea>
    @error('body', 'comment')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror

    <button type="submit" class="btn btn-accent comment-form__submit">
        {{ $parent ? 'Antwoord plaatsen' : 'Reactie plaatsen' }}
    </button>
</form>