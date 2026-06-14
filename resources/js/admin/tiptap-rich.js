import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableHeader } from '@tiptap/extension-table-header'
import { TableCell } from '@tiptap/extension-table-cell'
import Image from '@tiptap/extension-image'

/**
 * TipTap rich-profiel — voor Posts.
 * Extensions: StarterKit (incl. Link, Underline, CodeBlock) + Table-suite + Image (met align-attribute).
 *
 * Output: HTML, server-side gesaneerd via mews/purifier 'rich'-config.
 * Headings beperkt tot h2-h4 (h1 is gereserveerd voor paginatitel).
 *
 * Image-extensie (stap 4.6):
 * - inline: false → afbeeldingen zijn block-level
 * - allowBase64: false → alleen echte URL's (Purifier weigert base64 toch)
 * - Custom 'align'-attribute → class="img-align-{left|center|right|full}"
 *   Default is 'full'. Geen inline style — Purifier-allowlist heeft alleen [class].
 *
 * Modal-koppeling: via Alpine.store('imagePicker') (zie image-picker.js).
 * Editor roept openImagePicker() aan; modal roept insertImage(src, alt) op deze factory.
 */
const ImageWithAlign = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            align: {
                default: 'full',
                parseHTML: el => {
                    const cls = el.getAttribute('class') || ''
                    const match = cls.match(/img-align-(left|center|right|full)/)
                    return match ? match[1] : 'full'
                },
                renderHTML: attrs => {
                    if (!attrs.align) return {}
                    return { class: `img-align-${attrs.align}` }
                },
            },
        }
    },
})

export function tiptapRich() {
    return {
        editor: null,
        content: '',
        state: {
            bold: false, italic: false, underline: false, strike: false,
            h2: false, h3: false, h4: false,
            bulletList: false, orderedList: false, blockquote: false,
            code: false, codeBlock: false, link: false,
            table: false,
            image: false, imageAlign: null,
        },

        init() {
            if (this.editor) {
                return
            }
            this.content = this.$refs.hidden.value || ''

            this.editor = new Editor({
                element: this.$refs.editor,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [2, 3, 4] },
                        link: {
                            openOnClick: false,
                            autolink: true,
                            HTMLAttributes: {
                                rel: 'nofollow noopener',
                                target: '_blank',
                            },
                        },
                    }),
                    Table.configure({
                        resizable: false,
                        HTMLAttributes: { class: 'tiptap-table' },
                    }),
                    TableRow,
                    TableHeader,
                    TableCell,
                    ImageWithAlign.configure({
                        inline: false,
                        allowBase64: false,
                    }),
                ],
                content: this.content,
                onUpdate: ({ editor }) => {
                    this.content = editor.getHTML()
                    this.syncState()
                    this.syncHidden()
                },
                onSelectionUpdate: () => this.syncState(),
                onTransaction: () => this.syncState(),
            })

            this.syncHidden()
            this.syncState()
        },

        destroy() {
            this.editor?.destroy()
        },

        syncHidden() {
            this.$refs.hidden.value = this.content
        },

        syncState() {
            // Net als insertImage/setImageAlign/deleteImage moeten we Alpine's
            // reactivity Proxy hier omzeilen — anders geeft isActive('image')
            // intermittent false terug op nodes die wél een image zijn (zichtbaar
            // wanneer er meerdere images in het doc staan en je tussen ze switcht).
            const editor = window.Alpine.raw(this.editor)
            if (!editor || editor.isDestroyed) return

            this.state.bold        = editor.isActive('bold')
            this.state.italic      = editor.isActive('italic')
            this.state.underline   = editor.isActive('underline')
            this.state.strike      = editor.isActive('strike')
            this.state.h2          = editor.isActive('heading', { level: 2 })
            this.state.h3          = editor.isActive('heading', { level: 3 })
            this.state.h4          = editor.isActive('heading', { level: 4 })
            this.state.bulletList  = editor.isActive('bulletList')
            this.state.orderedList = editor.isActive('orderedList')
            this.state.blockquote  = editor.isActive('blockquote')
            this.state.code        = editor.isActive('code')
            this.state.codeBlock   = editor.isActive('codeBlock')
            this.state.link        = editor.isActive('link')
            this.state.table       = editor.isActive('table')
            this.state.image       = editor.isActive('image')
            this.state.imageAlign  = this.state.image
                ? (editor.getAttributes('image').align || 'full')
                : null
        },
        
        // Tekst-opmaak
        toggleBold()        { this.editor.chain().focus().toggleBold().run() },
        toggleItalic()      { this.editor.chain().focus().toggleItalic().run() },
        toggleUnderline()   { this.editor.chain().focus().toggleUnderline().run() },
        toggleStrike()      { this.editor.chain().focus().toggleStrike().run() },

        // Block-niveau
        setHeading(level)   { this.editor.chain().focus().toggleHeading({ level }).run() },
        setParagraph()      { this.editor.chain().focus().setParagraph().run() },
        toggleBulletList()  { this.editor.chain().focus().toggleBulletList().run() },
        toggleOrderedList() { this.editor.chain().focus().toggleOrderedList().run() },
        toggleBlockquote()  { this.editor.chain().focus().toggleBlockquote().run() },
        toggleCode()        { this.editor.chain().focus().toggleCode().run() },
        toggleCodeBlock()   { this.editor.chain().focus().toggleCodeBlock().run() },
        insertHorizontalRule() { this.editor.chain().focus().setHorizontalRule().run() },

        // Tabel
        insertTable() {
            this.editor.chain().focus()
                .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
                .run()
        },
        addColumnAfter() { this.editor.chain().focus().addColumnAfter().run() },
        addRowAfter()    { this.editor.chain().focus().addRowAfter().run() },
        deleteColumn()   { this.editor.chain().focus().deleteColumn().run() },
        deleteRow()      { this.editor.chain().focus().deleteRow().run() },
        deleteTable()    { this.editor.chain().focus().deleteTable().run() },

        // Undo/redo
        undo() { this.editor.chain().focus().undo().run() },
        redo() { this.editor.chain().focus().redo().run() },

        // Link
        setLink() {
            const previousUrl = this.editor.getAttributes('link').href
            const url = window.prompt('URL (laat leeg om link te verwijderen):', previousUrl ?? '')
            if (url === null) return
            if (url === '') {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run()
                return
            }
            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
        },

        // ─── Image (stap 4.6) ────────────────────────────────────────────
        openImagePicker() {
            // Postcontext wordt door de modal zelf gelezen uit data-attribuut.
            // We hoeven hier alleen de target-editor door te geven.
            window.Alpine.store('imagePicker').openFor(this)
        },

        // Callback aangeroepen door de modal na selectie/upload
        insertImage(src, alt = '') {
            if (!src) return

            // Alpine wikkelt this.editor in een Vue-reactivity Proxy. ProseMirror's
            // applyInner doet "tr.before.eq(state.doc)" — die identiteitscheck breekt
            // op proxied nodes, met "Applying a mismatched transaction" als gevolg.
            // Alpine.raw() unwrapt naar de echte Editor; we gebruiken daarna
            // view.state (niet editor.state, dat is een getter die de proxy
            // opnieuw triggert) zodat we volledig binnen de raw-instanties blijven.
            const rawEditor = window.Alpine.raw(this.editor)
            if (!rawEditor || rawEditor.isDestroyed) return

            const view = rawEditor.view
            const state = view.state
            const imageNode = state.schema.nodes.image.create({ src, alt, align: 'full' })
            view.dispatch(state.tr.replaceSelectionWith(imageNode))

            queueMicrotask(() => view.focus())
        },

        setImageAlign(align) {
            if (!this.state.image) return
            const rawEditor = window.Alpine.raw(this.editor)
            if (!rawEditor || rawEditor.isDestroyed) return
            rawEditor.chain().focus().updateAttributes('image', { align }).run()
        },

        deleteImage() {
            if (!this.state.image) return
            const rawEditor = window.Alpine.raw(this.editor)
            if (!rawEditor || rawEditor.isDestroyed) return
            rawEditor.chain().focus().deleteSelection().run()
        },
    }
}
