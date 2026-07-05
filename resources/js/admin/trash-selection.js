/**
 * Alpine.store voor bulk-selectie in de prullenbak (Stap 4.12.c).
 *
 * Afwijking t.o.v. mediaSelection: keys zijn composite strings "{type}:{id}"
 * omdat trash-IDs niet globally uniek zijn (Post.1 en Destination.1 kunnen
 * beide bestaan).
 *
 * Beslissing: F4-T6 (alleen bulk-restore, geen bulk-force-delete).
 */
export function registerTrashSelectionStore(Alpine) {
    Alpine.store('trashSelection', {
        selected: new Set(),
        visibleKeys: [],

        reset() {
            this.selected = new Set();
            this.visibleKeys = [...document.querySelectorAll('[data-trash-key]')]
                .map(el => el.dataset.trashKey);
        },

        key(type, id) {
            return `${type}:${id}`;
        },

        isSelected(type, id) {
            return this.selected.has(this.key(type, id));
        },

        toggle(type, id) {
            const k = this.key(type, id);
            if (this.selected.has(k)) {
                this.selected.delete(k);
            } else {
                this.selected.add(k);
            }
            // Force reactivity — Alpine wraps sets/maps in reactive proxies
            // maar mutaties op .add()/.delete() zijn niet altijd getriggerd zonder re-assign.
            this.selected = new Set(this.selected);
        },

        selectAllVisible() {
            this.selected = new Set(this.visibleKeys);
        },

        clear() {
            this.selected = new Set();
        },

        count() {
            return this.selected.size;
        },

        hasSelection() {
            return this.selected.size > 0;
        },

        allVisibleSelected() {
            return this.visibleKeys.length > 0
                && this.visibleKeys.every(k => this.selected.has(k));
        },

        async destroy() {
            if (this.selected.size === 0) return;

            const items = [...this.selected].map(k => {
                const [type, id] = k.split(':');
                return { type, id: parseInt(id, 10) };
            });

            const form = document.getElementById('trash-bulk-restore-form');
            const input = form.querySelector('input[name="items"]');
            input.value = JSON.stringify(items);
            form.submit();
        },
    });
}
