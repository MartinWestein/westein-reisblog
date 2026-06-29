@props(['mediaId'])

<div
    class="media-card__delete-overlay"
    style="position: absolute; top: 0.5rem; right: 0.5rem; z-index: 2;"
    x-data="{
        confirming: false,
        deleting: false,
        async destroy() {
            this.deleting = true;
            try {
                const response = await fetch('{{ route('admin.media.destroy', $mediaId) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) throw new Error('Delete failed: ' + response.status);
                this.$root.closest('.media-card-wrapper').remove();
            } catch (e) {
                console.error(e);
                this.deleting = false;
                this.confirming = false;
                alert('Verwijderen mislukt. Ververs de pagina en probeer opnieuw.');
            }
        }
    }"
    @click.outside="confirming = false"
>
    <button
        type="button"
        class="btn btn-sm btn-danger"
        x-show="!confirming && !deleting"
        @click="confirming = true"
        title="Verwijderen"
    >
        <i class="bi bi-trash"></i>
    </button>

    <div x-show="confirming && !deleting" x-cloak x-transition.opacity>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-danger" @click="destroy()" title="Bevestigen">
                <i class="bi bi-check-lg"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light" @click="confirming = false" title="Annuleren">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <span
        x-show="deleting"
        x-cloak
        class="spinner-border spinner-border-sm text-danger"
        role="status"
    ></span>
</div>
