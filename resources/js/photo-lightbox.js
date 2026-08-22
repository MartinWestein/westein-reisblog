// Publieke lightbox voor /fotos (5.3.c-ii). Verzamelt de foto-tegels binnen de
// x-data-root, opent een overlay met de grote versie + prev/next + caption +
// 'bekijk locatie'-link. Progressive enhancement: zonder JS blijven de tegels
// gewone links naar de location-detail.

export default function photoLightbox() {
    return {
        open: false,
        index: 0,
        items: [],

        init() {
            this.items = Array.from(this.$el.querySelectorAll('[data-full]')).map((el) => ({
                full: el.dataset.full,
                caption: el.dataset.caption ?? '',
                locationUrl: el.getAttribute('href'),
            }));
        },

        openAt(index) {
            this.index = index;
            this.open = true;
        },

        close() {
            this.open = false;
        },

        prev() {
            if (this.items.length === 0) return;
            this.index = (this.index - 1 + this.items.length) % this.items.length;
        },

        next() {
            if (this.items.length === 0) return;
            this.index = (this.index + 1) % this.items.length;
        },

        get current() {
            return this.items[this.index] ?? { full: '', caption: '', locationUrl: '#' };
        },
    };
}