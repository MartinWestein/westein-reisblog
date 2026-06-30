//**

export function registerMediaSelectionStore(Alpine) {
    Alpine.store('mediaSelection', {
        selected: new Set(),

        // Lifecycle reset — aangeroepen vanuit x-init in de grid-wrapper
        reset() {
            this.selected = new Set();
        },

        // Selectie-mutaties
        toggle(id) {
            const numericId = Number(id);
            if (this.selected.has(numericId)) {
                this.selected.delete(numericId);
            } else {
                this.selected.add(numericId);
            }
            // Force-notify: Alpine reactiviteit op Set is wankel (leerpunt #16/#47),
            // identity-change forceert herrender.
            this.selected = new Set(this.selected);
        },

        isSelected(id) {
            return this.selected.has(Number(id));
        },

        selectAllVisible() {
            document.querySelectorAll('[data-media-id]').forEach(el => {
                this.selected.add(Number(el.dataset.mediaId));
            });
            this.selected = new Set(this.selected);
        },

        clear() {
            this.selected = new Set();
        },

        // Computed (Alpine stores hebben geen native getters voor reactivity —
        // gewone methods, in templates aangeroepen als $store.mediaSelection.count())
        count() {
            return this.selected.size;
        },

        hasSelection() {
            return this.selected.size > 0;
        },

        allVisibleSelected() {
            const checkboxes = document.querySelectorAll('[data-media-id]');
            if (checkboxes.length === 0) return false;
            return [...checkboxes].every(el => this.selected.has(Number(el.dataset.mediaId)));
        },

        // Bulk-delete fetch — server redirect't naar admin.media.index met flash
        async destroy() {
            const ids = [...this.selected];
            const csrfToken = document.querySelector('meta[name=csrf-token]').content;

            try {
                const response = await fetch('/admin/media/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json, text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids }),
                });

                if (response.ok || response.redirected) {
                    window.location.href = response.url || window.location.href;
                } else {
                    throw new Error('Bulk delete failed: ' + response.status);
                }
            } catch (e) {
                console.error(e);
                alert('Verwijderen mislukt. Ververs de pagina en probeer opnieuw.');
            }
        },
    });
}
