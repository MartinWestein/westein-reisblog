/**
 * Alpine.store voor bulk-selectie op de gebruikers-index (Stap 4.13.g).
 *
 * User-IDs zijn globally unique, dus keys zijn plain integer-strings
 * (afwijking van trashSelection die composite keys gebruikt).
 *
 * Twee bulk-acties: destroyDeactivate() en destroyReactivate() (F4-U9).
 * Beide submitten naar een eigen hidden form.
 */
export function registerUserSelectionStore(Alpine) {
    Alpine.store('userSelection', {
        selected: new Set(),
        visibleKeys: [],

        reset() {
            this.selected = new Set();
            this.visibleKeys = [...document.querySelectorAll('[data-user-id]')]
                .map(el => el.dataset.userId);
        },

        isSelected(id) {
            return this.selected.has(String(id));
        },

        toggle(id) {
            const k = String(id);
            if (this.selected.has(k)) {
                this.selected.delete(k);
            } else {
                this.selected.add(k);
            }
            // Force reactivity - Alpine's Proxy triggert niet altijd op .add()/.delete()
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

        destroyDeactivate() {
            this._submitForm('users-bulk-deactivate-form');
        },

        destroyReactivate() {
            this._submitForm('users-bulk-reactivate-form');
        },

        _submitForm(formId) {
            if (this.selected.size === 0) return;

            const ids = [...this.selected].map(k => parseInt(k, 10));

            const form = document.getElementById(formId);
            const input = form.querySelector('input[name="ids"]');
            input.value = JSON.stringify(ids);
            form.submit();
        },
    });
}
