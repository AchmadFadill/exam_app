@props([
    'placeholder' => 'Tulis konten...',
    'minHeight' => '180px',
    'label' => null,
    'showPreview' => true,
])

@php
    $wireModel = $attributes->wire('model')->value();
@endphp

<div
    x-data="tiptapEditorComponent({
        content: @entangle($wireModel),
        placeholder: @js($placeholder),
        minHeight: @js($minHeight),
    })"
    x-init="init()"
    class="space-y-3"
>
    @if($label)
        <label class="block text-xs font-black text-text-main uppercase tracking-widest opacity-70 italic">{{ $label }}</label>
    @endif

    <div class="rounded-2xl border border-border-main bg-white dark:bg-slate-900 overflow-hidden shadow-inner">
        <div class="flex flex-wrap items-center gap-2 px-3 py-2 border-b border-border-main bg-gray-50 dark:bg-slate-800">
            <button type="button" @mousedown.prevent="toggleBold()" :class="btnClass(toolbarState.bold)" class="px-2.5 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-lg">B</button>
            <button type="button" @mousedown.prevent="toggleItalic()" :class="btnClass(toolbarState.italic)" class="px-2.5 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-lg italic">I</button>
            <button type="button" @mousedown.prevent="toggleUnderline()" :class="btnClass(toolbarState.underline)" class="px-2.5 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-lg underline">U</button>

            <span class="w-px h-6 bg-border-main mx-1"></span>
            <button type="button" @mousedown.prevent="toggleBulletList()" :class="btnClass(toolbarState.bulletList)" class="px-2.5 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-lg">&bull; List</button>
            <button type="button" @mousedown.prevent="toggleOrderedList()" :class="btnClass(toolbarState.orderedList)" class="px-2.5 py-1.5 text-[11px] font-black uppercase tracking-widest rounded-lg">1. List</button>

            <span class="w-px h-6 bg-border-main mx-1"></span>

        </div>

        <div wire:ignore class="relative">
            <div
                x-ref="editor"
                data-latex-enabled="1"
                x-on:keydown="maintainTypingMarks($event)"
                x-on:mouseup="maintainTypingMarks()"
                x-on:keyup="storeSelection()"
                x-on:focus="storeSelection()"
                x-on:blur="storeSelection()"
                x-on:paste.prevent="handlePaste($event)"
                class="prose prose-sm max-w-none px-5 py-4 text-text-main focus:outline-none [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-6 [&_ol]:pl-6"
                :style="`min-height: ${minHeight}`"
            ></div>
        </div>
    </div>

    <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest">
        Mendukung LaTeX inline seperti <code>$x^2$</code> dan block <code>$$\\frac{a}{b}$$</code>.
    </p>

    @if($showPreview)
        <div x-show="hasLatex()" class="rounded-2xl border border-primary/20 bg-primary/[0.03] p-4 space-y-2">
            <p class="text-[10px] font-black text-primary uppercase tracking-widest">Preview Rumus</p>
            <div x-ref="preview" class="prose prose-sm max-w-none text-text-main [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-6 [&_ol]:pl-6"></div>
        </div>
    @endif
</div>

@once
    <style>
        [contenteditable][data-placeholder]:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
            pointer-events: none;
        }
    </style>
    @push('scripts')
        <script>
            window.tiptapEditorComponent = function (config) {
                return {
                    lastRange: null,
                    content: config.content ?? '',
                    placeholder: config.placeholder ?? 'Tulis konten...',
                    minHeight: config.minHeight ?? '180px',
                    toolbarState: {
                        bold: false,
                        italic: false,
                        underline: false,
                        bulletList: false,
                        orderedList: false,
                    },
                    async init() {
                        this.initEditor();
                        this.renderPreview();

                        this.$watch('content', (value) => {
                            const current = this.getHtml();
                            const next = value || '';
                            if (current !== next) {
                                this.setHtml(next || '');
                            }
                            this.renderPreview();
                        });
                    },

                    initEditor() {
                        this.$refs.editor.setAttribute('contenteditable', 'true');
                        this.$refs.editor.innerHTML = this.content || '';
                        this.$refs.editor.dataset.placeholder = this.placeholder;
                        this.$refs.editor.classList.add('focus:outline-none');
                        this.$refs.editor.addEventListener('input', () => {
                            this.content = this.$refs.editor.innerHTML;
                            this.storeSelection();
                            this.renderPreview();
                        });
                        this.$refs.editor.addEventListener('mouseup', () => this.storeSelection());
                    },

                    getHtml() {
                        return this.$refs.editor?.innerHTML || '';
                    },

                    setHtml(html) {
                        if (!this.$refs.editor) return;
                        this.$refs.editor.innerHTML = html || '';
                    },

                    hasLatex() {
                        const text = (this.content || '').replace(/<[^>]*>/g, ' ');
                        return text.includes('$')
                            || text.includes('\\(')
                            || text.includes('\\[')
                            || this.looksLikeLatex(text);
                    },

                    renderPreview() {
                        if (!this.$refs.preview) return;

                        const html = this.content || '';
                        const plainText = html.replace(/<[^>]*>/g, ' ').trim();

                        if (this.looksLikeLatex(plainText) && !this.hasExplicitLatexDelimiters(plainText)) {
                            this.$refs.preview.innerHTML = '';

                            this.$nextTick(() => {
                                if (window.katex && typeof window.katex.render === 'function') {
                                    window.katex.render(plainText, this.$refs.preview, {
                                        throwOnError: false,
                                        displayMode: false,
                                    });
                                    return;
                                }

                                this.$refs.preview.textContent = plainText;
                            });

                            return;
                        }

                        this.$refs.preview.innerHTML = html;
                        this.$nextTick(() => {
                            if (typeof window.renderKatexIn === 'function') {
                                window.renderKatexIn(this.$refs.preview);
                                return;
                            }

                            if (typeof window.renderMathInElement === 'function') {
                                window.renderMathInElement(this.$refs.preview, {
                                    delimiters: [
                                        { left: '$$', right: '$$', display: true },
                                        { left: '$', right: '$', display: false },
                                        { left: '\\[', right: '\\]', display: true },
                                        { left: '\\(', right: '\\)', display: false },
                                    ],
                                    throwOnError: false,
                                });
                            }
                        });
                    },

                    hasExplicitLatexDelimiters(text) {
                        return text.includes('$') || text.includes('\\(') || text.includes('\\[');
                    },

                    looksLikeLatex(text) {
                        if (!text) return false;

                        return /\\[a-zA-Z]+/.test(text)
                            || /[_^]\{[^}]+\}/.test(text)
                            || /[_^][A-Za-z0-9]/.test(text);
                    },

                    handlePaste(event) {
                        let pastedText = event?.clipboardData?.getData('text/plain') || '';
                        if (!pastedText) return;

                        pastedText = pastedText.trim();

                        if (this.looksLikeLatex(pastedText) && !this.hasExplicitLatexDelimiters(pastedText)) {
                            pastedText = `$${pastedText}$`;
                        }

                        this.restoreSelection();
                        this.$refs.editor.focus();

                        let inserted = false;
                        try {
                            inserted = document.execCommand('insertText', false, pastedText);
                        } catch (e) {
                            inserted = false;
                        }

                        if (!inserted) {
                            const sel = window.getSelection();
                            if (!sel) return;

                            let range;
                            if (sel.rangeCount > 0) {
                                range = sel.getRangeAt(0);
                            } else {
                                range = document.createRange();
                                range.selectNodeContents(this.$refs.editor);
                                range.collapse(false);
                                sel.removeAllRanges();
                                sel.addRange(range);
                            }

                            range.deleteContents();
                            const textNode = document.createTextNode(pastedText);
                            range.insertNode(textNode);
                            range.setStartAfter(textNode);
                            range.collapse(true);
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }

                        this.content = this.$refs.editor.innerHTML;
                        this.storeSelection();
                        this.renderPreview();
                    },

                    btnClass(active) {
                        return active
                            ? 'bg-primary text-white border border-primary shadow-md shadow-primary/20'
                            : 'bg-white dark:bg-slate-900 text-text-main border border-border-main hover:border-primary hover:text-primary transition-colors';
                    },

                    focus() {
                        this.$refs.editor?.focus();
                    },

                    toggleBold() {
                        this.toolbarState.bold = !this.toolbarState.bold;
                        this.applyInlineMarks();
                    },

                    toggleItalic() {
                        this.toolbarState.italic = !this.toolbarState.italic;
                        this.applyInlineMarks();
                    },

                    toggleUnderline() {
                        this.toolbarState.underline = !this.toolbarState.underline;
                        this.applyInlineMarks();
                    },

                    toggleBulletList() {
                        this.restoreSelection();
                        this.focusEditorEndIfNeeded();
                        this.execListCommand('insertUnorderedList', 'ul');
                        this.toolbarState.bulletList = true;
                        this.toolbarState.orderedList = false;
                        this.content = this.$refs.editor.innerHTML;
                        this.storeSelection();
                    },

                    toggleOrderedList() {
                        this.restoreSelection();
                        this.focusEditorEndIfNeeded();
                        this.execListCommand('insertOrderedList', 'ol');
                        this.toolbarState.orderedList = true;
                        this.toolbarState.bulletList = false;
                        this.content = this.$refs.editor.innerHTML;
                        this.storeSelection();
                    },

                    maintainTypingMarks(event = null) {
                        // Do not interfere with delete/backspace or active text selections.
                        if (event && (event.key === 'Backspace' || event.key === 'Delete')) {
                            return;
                        }

                        const sel = window.getSelection();
                        if (sel && sel.rangeCount > 0 && !sel.getRangeAt(0).collapsed) {
                            return;
                        }

                        // Keep typing mode stable: if toolbar is active, re-apply marks at caret.
                        this.applyInlineMarks();
                    },

                    applyInlineMarks() {
                        this.restoreSelection();
                        this.$refs.editor.focus();
                        this.setFallbackCommandState('bold', this.toolbarState.bold);
                        this.setFallbackCommandState('italic', this.toolbarState.italic);
                        this.setFallbackCommandState('underline', this.toolbarState.underline);
                        this.storeSelection();
                    },

                    setFallbackCommandState(command, desiredState) {
                        try {
                            const current = document.queryCommandState(command);
                            if (Boolean(current) !== Boolean(desiredState)) {
                                document.execCommand(command);
                            }
                        } catch (e) {
                            // Ignore unsupported command state checks.
                        }
                    },

                    storeSelection() {
                        const sel = window.getSelection();
                        if (!sel || sel.rangeCount === 0) return;
                        const range = sel.getRangeAt(0);
                        if (this.$refs.editor.contains(range.commonAncestorContainer)) {
                            this.lastRange = range.cloneRange();
                        }
                    },

                    restoreSelection() {
                        if (!this.lastRange) return;
                        const sel = window.getSelection();
                        if (!sel) return;
                        sel.removeAllRanges();
                        sel.addRange(this.lastRange);
                    },

                    focusEditorEndIfNeeded() {
                        const sel = window.getSelection();
                        if (!sel) {
                            this.$refs.editor?.focus();
                            return;
                        }
                        const hasRange = sel && sel.rangeCount > 0;
                        const inEditor = hasRange
                            ? this.$refs.editor.contains(sel.getRangeAt(0).commonAncestorContainer)
                            : false;

                        if (inEditor) {
                            this.$refs.editor.focus();
                            return;
                        }

                        this.$refs.editor.focus();
                        const range = document.createRange();
                        range.selectNodeContents(this.$refs.editor);
                        range.collapse(false);
                        sel.removeAllRanges();
                        sel.addRange(range);
                        this.storeSelection();
                    },

                    execListCommand(command, tag) {
                        const before = this.$refs.editor.innerHTML;
                        this.$refs.editor.focus();
                        document.execCommand(command);

                        // Some environments ignore execCommand on contenteditable.
                        if (this.$refs.editor.innerHTML === before) {
                            this.insertEmptyListAtCaret(tag);
                        }
                    },

                    insertEmptyListAtCaret(tag) {
                        const sel = window.getSelection();
                        if (!sel) return;

                        let range;
                        if (sel.rangeCount > 0) {
                            range = sel.getRangeAt(0);
                        } else {
                            range = document.createRange();
                            range.selectNodeContents(this.$refs.editor);
                            range.collapse(false);
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }

                        if (!this.$refs.editor.contains(range.commonAncestorContainer)) {
                            this.focusEditorEndIfNeeded();
                            if (!sel.rangeCount) return;
                            range = sel.getRangeAt(0);
                        }

                        const list = document.createElement(tag);
                        const li = document.createElement('li');
                        li.appendChild(document.createElement('br'));
                        list.appendChild(li);

                        range.deleteContents();
                        range.insertNode(list);

                        const caret = document.createRange();
                        caret.setStart(li, 0);
                        caret.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(caret);
                    },

                };
            };
        </script>
    @endpush
@endonce

