/**
 * Alpine data factory voor <x-admin.image-upload>.
 * Beheert: drag-state, preview-URL, client-side validatie (size, dimensions, MIME).
 *
 * Server-side blijft de file via een standaard <input type="file"> binnenkomen.
 * Dit script schrijft de FileList van een drop-event terug op de input zodat
 * de form-submit zonder JS-bypass werkt.
 */
export function imageUpload(config) {
    return {
        // Config (geinjecteerd vanuit Blade)
        inputId: config.inputId,
        maxBytes: config.maxBytes,
        minWidth: config.minWidth,
        minHeight: config.minHeight,
        accept: config.accept,
        hasCurrent: config.hasCurrent,
        currentUrl: config.currentUrl,

        // State
        dragging: false,
        previewUrl: null,
        clientError: null,
        markedForRemoval: false,

        handleDrop(event) {
            this.dragging = false;
            const files = event.dataTransfer?.files;
            if (!files || files.length === 0) return;
            this.processFile(files[0]);
        },

        handleFileChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            this.processFile(file);
        },

        async processFile(file) {
            this.clientError = null;

            // MIME-check
            if (!this.accept.includes(file.type)) {
                this.clientError = 'Onverwacht bestandstype. Kies een JPG, PNG of WebP.';
                this.resetInput();
                return;
            }

            // Size-check
            if (file.size > this.maxBytes) {
                const maxMb = (this.maxBytes / 1024 / 1024).toFixed(0);
                this.clientError = `Bestand is groter dan ${maxMb} MB.`;
                this.resetInput();
                return;
            }

            // Dimensions-check (async — laadt afbeelding)
            try {
                const { width, height } = await this.readDimensions(file);
                if (width < this.minWidth || height < this.minHeight) {
                    this.clientError = `Afbeelding is te klein. Minimaal ${this.minWidth}×${this.minHeight} pixels (deze is ${width}×${height}).`;
                    this.resetInput();
                    return;
                }
            } catch (e) {
                this.clientError = 'Kon de afbeelding niet lezen.';
                this.resetInput();
                return;
            }

            // Alles ok → preview tonen
            this.previewUrl = URL.createObjectURL(file);
            this.markedForRemoval = false;

            // Bij drop: schrijf de FileList ook naar de input
            const input = document.getElementById(this.inputId);
            if (input && input.files !== this.getFilesFromFile(file)) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }
        },

        readDimensions(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve({ width: img.naturalWidth, height: img.naturalHeight });
                };
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('image-load-failed'));
                };
                img.src = url;
            });
        },

        clearSelection() {
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
            }
            this.previewUrl = null;
            this.clientError = null;
            this.resetInput();
        },

        resetInput() {
            const input = document.getElementById(this.inputId);
            if (input) input.value = '';
        },

        getFilesFromFile(file) {
            // Helper voor de FileList-equality check; in praktijk altijd false
            return null;
        },
    };
}
