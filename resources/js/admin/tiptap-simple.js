import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Alpine from 'alpinejs'

/**
 * TipTap simple-profiel — voor Pages en Newsletter.
 * Extensions: StarterKit (bold/italic/headings/lists/blockquote/code/HR/undo)
 *           + Link + Underline.
 *
 * Output: HTML, server-side gesaneerd via mews/purifier 'simple'-config.
 * Headings beperkt tot h2-h4 (h1 is gereserveerd voor paginatitel in layout).
 *
 * Belangrijk (leerpunt #34): Alpine wikkelt this.editor in een Vue-reactivity
 * Proxy. ProseMirror gebruikt identity-checks intern en gooit dan
 * "Applying a mismatched transaction" bij elke dispatch. Daarom doen alle
 * command-methods Alpine.raw(...) via de chain()-helper.
 */
export function tiptapSimple() {
    return {
        editor: null,
        content: '',
        state: {
            bold: false, italic: false, underline: false, strike: false,
            h2: false, h3: false, h4: false,
            bulletList: false, orderedList: false, blockquote: false,
            code: false, link: false,
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
            Alpine.raw(this.editor)?.destroy()
        },

        syncHidden() {
            this.$refs.hidden.value = this.content
        },

        syncState() {
            const e = Alpine.raw(this.editor)
            if (!e) return
            this.state.bold        = e.isActive('bold')
            this.state.italic      = e.isActive('italic')
            this.state.underline   = e.isActive('underline')
            this.state.strike      = e.isActive('strike')
            this.state.h2          = e.isActive('heading', { level: 2 })
            this.state.h3          = e.isActive('heading', { level: 3 })
            this.state.h4          = e.isActive('heading', { level: 4 })
            this.state.bulletList  = e.isActive('bulletList')
            this.state.orderedList = e.isActive('orderedList')
            this.state.blockquote  = e.isActive('blockquote')
            this.state.code        = e.isActive('code')
            this.state.link        = e.isActive('link')
        },

        // ProseMirror-transactions moeten op de RAW editor — niet op de Alpine-Proxy.
        chain() {
            return Alpine.raw(this.editor).chain().focus()
        },

        toggleBold()        { this.chain().toggleBold().run() },
        toggleItalic()      { this.chain().toggleItalic().run() },
        toggleUnderline()   { this.chain().toggleUnderline().run() },
        toggleStrike()      { this.chain().toggleStrike().run() },
        setHeading(level)   { this.chain().toggleHeading({ level }).run() },
        setParagraph()      { this.chain().setParagraph().run() },
        toggleBulletList()  { this.chain().toggleBulletList().run() },
        toggleOrderedList() { this.chain().toggleOrderedList().run() },
        toggleBlockquote()  { this.chain().toggleBlockquote().run() },
        toggleCode()        { this.chain().toggleCode().run() },
        undo()              { this.chain().undo().run() },
        redo()              { this.chain().redo().run() },

        setLink() {
            const editor = Alpine.raw(this.editor)
            const previousUrl = editor.getAttributes('link').href
            const url = window.prompt('URL (laat leeg om link te verwijderen):', previousUrl ?? '')
            if (url === null) return
            if (url === '') {
                this.chain().extendMarkRange('link').unsetLink().run()
                return
            }
            this.chain().extendMarkRange('link').setLink({ href: url }).run()
        },
    }
}
