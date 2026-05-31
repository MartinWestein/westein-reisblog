import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'

/**
 * TipTap simple-profiel — voor Pages en Newsletter.
 * Extensions: StarterKit (bold/italic/headings/lists/blockquote/code/HR/undo)
 *           + Link + Underline.
 *
 * Output: HTML, server-side gesaneerd via mews/purifier 'simple'-config.
 * Headings beperkt tot h2-h4 (h1 is gereserveerd voor paginatitel in layout).
 */
export function tiptapSimple() {
    return {
        editor: null,
        content: '',
        // Reactieve state voor toolbar-buttons (active/disabled)
        state: {
            bold: false, italic: false, underline: false, strike: false,
            h2: false, h3: false, h4: false,
            bulletList: false, orderedList: false, blockquote: false,
            code: false, link: false,
        },

            init() {
                // Defensief: voorkom dubbele initialisatie (kan optreden als Alpine
                // init() en een x-init beide draaien, of bij hot-reload)
                if (this.editor) {
                    return
                }
            // Bron-van-waarheid voor initiële content: het hidden field zelf
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
            this.editor?.destroy()
        },

        syncHidden() {
            // Schrijf actuele HTML naar het hidden field zodat de form 'm submitten
            this.$refs.hidden.value = this.content
        },

        syncState() {
            const e = this.editor
            if (!e) return
            this.state.bold       = e.isActive('bold')
            this.state.italic     = e.isActive('italic')
            this.state.underline  = e.isActive('underline')
            this.state.strike     = e.isActive('strike')
            this.state.h2         = e.isActive('heading', { level: 2 })
            this.state.h3         = e.isActive('heading', { level: 3 })
            this.state.h4         = e.isActive('heading', { level: 4 })
            this.state.bulletList = e.isActive('bulletList')
            this.state.orderedList= e.isActive('orderedList')
            this.state.blockquote = e.isActive('blockquote')
            this.state.code       = e.isActive('code')
            this.state.link       = e.isActive('link')
        },

        // Commando's — aangeroepen vanuit de toolbar
        toggleBold()        { this.editor.chain().focus().toggleBold().run() },
        toggleItalic()      { this.editor.chain().focus().toggleItalic().run() },
        toggleUnderline()   { this.editor.chain().focus().toggleUnderline().run() },
        toggleStrike()      { this.editor.chain().focus().toggleStrike().run() },
        setHeading(level)   { this.editor.chain().focus().toggleHeading({ level }).run() },
        setParagraph()      { this.editor.chain().focus().setParagraph().run() },
        toggleBulletList()  { this.editor.chain().focus().toggleBulletList().run() },
        toggleOrderedList() { this.editor.chain().focus().toggleOrderedList().run() },
        toggleBlockquote()  { this.editor.chain().focus().toggleBlockquote().run() },
        toggleCode()        { this.editor.chain().focus().toggleCode().run() },
        undo()              { this.editor.chain().focus().undo().run() },
        redo()              { this.editor.chain().focus().redo().run() },

        setLink() {
            const previousUrl = this.editor.getAttributes('link').href
            const url = window.prompt('URL (laat leeg om link te verwijderen):', previousUrl ?? '')
            if (url === null) return                                  // cancel
            if (url === '') {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run()
                return
            }
            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
        },
    }
}
