/**
 * Tag-pills met autocomplete — voor het Post-formulier.
 *
 * Eén hidden field (komma-gescheiden) is de bron-van-waarheid voor submit;
 * de Form Request splitst 'm server-side weer naar een array.
 *
 * Args:
 *   initial   — komma-gescheiden string van reeds-gekozen tags (uit old() of model)
 *   available — JSON-array van alle bestaande tag-namen (voor autocomplete)
 */
export function tagPills(initial = '', available = []) {
    return {
        tags: [],
        all: [],
        query: '',
        open: false,
        activeIndex: -1,

        init() {
            this.all = Array.isArray(available) ? available : []
            this.tags = (initial || '')
                .split(',')
                .map(t => t.trim())
                .filter(Boolean)
            this.syncHidden()
        },

        // Suggesties: bestaande tags die matchen, nog niet gekozen, max 8
        get suggestions() {
            const q = this.query.trim().toLowerCase()
            if (q === '') return []
            return this.all
                .filter(t => t.toLowerCase().includes(q))
                .filter(t => !this.tags.some(chosen => chosen.toLowerCase() === t.toLowerCase()))
                .slice(0, 8)
        },

        addTag(name) {
            const clean = (name ?? this.query).trim()
            if (clean === '') return
            // Dedupe case-insensitive (server lowercaset alsnog)
            if (!this.tags.some(t => t.toLowerCase() === clean.toLowerCase())) {
                this.tags.push(clean)
            }
            this.query = ''
            this.open = false
            this.activeIndex = -1
            this.syncHidden()
        },

        removeTag(index) {
            this.tags.splice(index, 1)
            this.syncHidden()
        },

        // Enter = kies actieve suggestie of voeg vrije invoer toe; komma idem
        onEnter() {
            if (this.activeIndex >= 0 && this.suggestions[this.activeIndex]) {
                this.addTag(this.suggestions[this.activeIndex])
            } else {
                this.addTag()
            }
        },

        // Backspace op leeg veld verwijdert laatste pill
        onBackspace() {
            if (this.query === '' && this.tags.length > 0) {
                this.removeTag(this.tags.length - 1)
            }
        },

        moveDown() {
            if (this.suggestions.length === 0) return
            this.open = true
            this.activeIndex = Math.min(this.activeIndex + 1, this.suggestions.length - 1)
        },

        moveUp() {
            this.activeIndex = Math.max(this.activeIndex - 1, -1)
        },

        syncHidden() {
            this.$refs.hidden.value = this.tags.join(',')
        },
    }
}
