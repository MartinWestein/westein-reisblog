/**
 * Alpine data factory voor <x-admin.gallery-upload>.
 * Beheert: multi-file drag-drop upload, SortableJS-reorder, per-foto delete.
 *
 * Alle acties lopen via AJAX (los van de form-submit):
 *   - upload   → POST   /admin/media/upload
 *   - reorder  → PATCH  /admin/media/reorder
 *   - delete   → DELETE /admin/media/{id}
 */
import Sortable from 'sortablejs';

export function galleryUpload(config) {
    return {
        // Config (geïnjecteerd vanuit Blade)
        modelType: config.modelType,
        modelId: config.modelId,
        collection: config.collection,
        uploadUrl: config.uploadUrl,
        reorderUrl: config.reorderUrl,
        destroyUrlBase: config.destroyUrlBase, // bv. '/admin/media/' → + id
        maxBytes: config.maxBytes,
        accept: config.accept,

        // State
        items: config.items, // [{ id, url, name }]
        dragging: false,
        uploading: false,
        error: null,
        confirmingId: null,

        init() {
            this.$nextTick(() => this.initSortable());
        },

        get csrf() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },

        initSortable() {
            const grid = this.$refs.grid;
            if (!grid) return;

            Sortable.create(grid, {
                animation: 150,
                handle: '.gallery-upload__handle',
                onEnd: () => this.persistOrder(),
            });
        },

        // ---- Upload ----------------------------------------------------

        openPicker() {
            this.$refs.input.click();
        },

        handleDrop(event) {
            this.dragging = false;
            this.uploadFiles(event.dataTransfer.files);
        },

        handleFileChange(event) {
            this.uploadFiles(event.target.files);
            event.target.value = ''; // reset zodat dezelfde file opnieuw kan
        },

        async uploadFiles(fileList) {
            this.error = null;
            const files = Array.from(fileList);
            if (files.length === 0) return;

            // Client-side validatie
            for (const file of files) {
                if (!this.accept.includes(file.type)) {
                    this.error = `Bestandstype niet toegestaan: ${file.name}`;
                    return;
                }
                if (file.size > this.maxBytes) {
                    this.error = `Bestand te groot: ${file.name}`;
                    return;
                }
            }

            const formData = new FormData();
            formData.append('model_type', this.modelType);
            formData.append('model_id', this.modelId);
            formData.append('collection', this.collection);
            files.forEach((file) => formData.append('files[]', file));

            this.uploading = true;
            try {
                const res = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    this.error = data.message || 'Uploaden mislukt.';
                    return;
                }

                const data = await res.json();
                this.items.push(...data.media);
            } catch (e) {
                this.error = 'Netwerkfout bij uploaden.';
            } finally {
                this.uploading = false;
            }
        },

        // ---- Reorder ---------------------------------------------------

        async persistOrder() {
            // Lees de DOM-volgorde van de grid-kinderen
            const ids = Array.from(this.$refs.grid.children)
                .map((el) => parseInt(el.dataset.mediaId, 10))
                .filter((id) => !isNaN(id));

            try {
                await fetch(this.reorderUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids }),
                });
                // Sync de lokale state met de nieuwe volgorde
                this.items.sort((a, b) => ids.indexOf(a.id) - ids.indexOf(b.id));
            } catch (e) {
                this.error = 'Volgorde opslaan mislukt.';
            }
        },

        // ---- Delete ----------------------------------------------------

        askDelete(id) {
            this.confirmingId = id;
        },

        cancelDelete() {
            this.confirmingId = null;
        },

        async confirmDelete(id) {
            try {
                const res = await fetch(this.destroyUrlBase + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    this.items = this.items.filter((m) => m.id !== id);
                } else {
                    this.error = 'Verwijderen mislukt.';
                }
            } catch (e) {
                this.error = 'Netwerkfout bij verwijderen.';
            } finally {
                this.confirmingId = null;
            }
        },
    };
}
