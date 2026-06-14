/**
 * Image-picker modal voor de TipTap rich-editor (stap 4.6).
 *
 * Twee componenten:
 *
 * 1. Alpine.store('imagePicker') — cross-component coördinatie
 *    open: boolean
 *    targetEditor: tiptapRich-factory instance (heeft insertImage())
 *    openFor(editor): door editor aangeroepen om modal te openen
 *    close(): door modal aangeroepen
 *    selectImage(src, alt): door modal aangeroepen na browse/upload
 *
 * 2. Alpine.data('imagePickerModal') — lokale state per modal-instance
 *    activeTab: 'browse' | 'upload'
 *    Browse: items, search, collectionFilter, loading, nextCursor
 *    Upload: uploading, uploadError, dragging
 */

document.addEventListener('alpine:init', () => {
    window.Alpine.store('imagePicker', {
        open: false,
        targetEditor: null,

        openFor(editor) {
            this.targetEditor = editor
            this.open = true
        },

        close() {
            this.open = false
            // targetEditor pas opruimen ná close-animatie? Voor nu direct:
            this.targetEditor = null
        },

        selectImage(src, alt = '') {
            try {
                if (this.targetEditor) {
                    this.targetEditor.insertImage(src, alt)
                }
            } catch (err) {
                console.error('Image insertion failed in store.selectImage:', err)
            } finally {
                this.close()
            }
        },
    })

    window.Alpine.data('imagePickerModal', (config = {}) => ({
        // Config uit Blade
        postId: config.postId ?? null,
        browseUrl: config.browseUrl ?? '/admin/media-picker',
        uploadUrl: config.uploadUrl ?? null,
        csrfToken: config.csrfToken ?? '',

        // Tab-state
        activeTab: 'browse',

        // Browse-state
        items: [],
        search: '',
        collectionFilter: '',  // '', 'gallery', 'hero', 'featured', 'inline_images'
        loading: false,
        loadError: '',
        nextCursor: null,
        hasFetched: false,

        // Upload-state
        uploading: false,
        uploadError: '',
        uploadAlt: '',
        dragging: false,

        init() {
            // Lui laden: pas fetchen wanneer de modal voor het eerst opent.
            this.$watch('$store.imagePicker.open', (isOpen) => {
                if (isOpen && !this.hasFetched && this.activeTab === 'browse') {
                    this.fetchItems({ reset: true })
                }
                if (!isOpen) {
                    // Reset upload-state bij sluiten; browse-state bewaren
                    // (zodat browse-grid niet flikkert bij volgende keer openen).
                    this.uploadError = ''
                    this.uploadAlt = ''
                }
            })

            // Bij tab-switch naar browse en nog niet gefetcht: laad.
            this.$watch('activeTab', (tab) => {
                if (tab === 'browse' && !this.hasFetched) {
                    this.fetchItems({ reset: true })
                }
            })
        },

        get canUpload() {
            return this.postId !== null
        },

        async fetchItems({ reset = false } = {}) {
            if (this.loading) return
            this.loading = true
            this.loadError = ''

            try {
                const params = new URLSearchParams()
                if (this.search) params.set('search', this.search)
                if (this.collectionFilter) params.set('collection', this.collectionFilter)
                if (!reset && this.nextCursor) params.set('cursor', this.nextCursor)

                const url = `${this.browseUrl}?${params.toString()}`
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`)
                }

                const data = await response.json()
                this.items = reset ? data.items : [...this.items, ...data.items]
                this.nextCursor = data.next_cursor ?? null
                this.hasFetched = true
            } catch (err) {
                this.loadError = 'Afbeeldingen konden niet worden geladen. Probeer opnieuw.'
                console.error('Image picker fetch error:', err)
            } finally {
                this.loading = false
            }
        },

        applyFilters() {
            this.items = []
            this.nextCursor = null
            this.hasFetched = false
            this.fetchItems({ reset: true })
        },

        loadMore() {
            if (this.nextCursor) this.fetchItems()
        },

        pickItem(item) {
            window.Alpine.store('imagePicker').selectImage(item.url, item.alt || '')
        },

        // ─── Upload ──────────────────────────────────────────────────────
        onDragOver(e) {
            if (!this.canUpload) return
            e.preventDefault()
            this.dragging = true
        },

        onDragLeave() {
            this.dragging = false
        },

        onDrop(e) {
            if (!this.canUpload) return
            e.preventDefault()
            this.dragging = false
            const file = e.dataTransfer?.files?.[0]
            if (file) this.uploadFile(file)
        },

        onFilePick(e) {
            const file = e.target.files?.[0]
            if (file) this.uploadFile(file)
        },

        async uploadFile(file) {
            if (!this.canUpload || !this.uploadUrl) return

            const allowed = ['image/jpeg', 'image/png', 'image/webp']
            if (!allowed.includes(file.type)) {
                this.uploadError = 'Alleen JPEG, PNG of WebP toegestaan.'
                return
            }
            // 16MB hard cap (matcht php.ini upload_max_filesize)
            if (file.size > 16 * 1024 * 1024) {
                this.uploadError = 'Bestand is te groot (max. 16 MB).'
                return
            }

            this.uploading = true
            this.uploadError = ''

            try {
                const formData = new FormData()
                formData.append('image', file)
                if (this.uploadAlt) formData.append('alt', this.uploadAlt)

                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    credentials: 'same-origin',
                    body: formData,
                })

                if (!response.ok) {
                    if (response.status === 422) {
                        const errors = await response.json()
                        const first = Object.values(errors.errors || {})[0]?.[0]
                        throw new Error(first || 'Validatiefout.')
                    }
                    throw new Error(`Upload mislukt (HTTP ${response.status})`)
                }

                const data = await response.json()
                window.Alpine.store('imagePicker').selectImage(data.url, data.alt || '')
            } catch (err) {
                this.uploadError = err.message || 'Upload mislukt.'
                console.error('Image upload error:', err)
            } finally {
                this.uploading = false
            }
        },
    }))
})
