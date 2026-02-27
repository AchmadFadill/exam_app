<div x-data="latexGuideModal()" @open-latex-guide.window="openAndRender()">
    <div x-cloak x-show="open" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-4xl max-h-[88vh] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/70">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Panduan Rumus</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tutorial lengkap menulis rumus. Klik tombol sisipkan untuk memasukkan format otomatis.</p>
                </div>
                <button type="button" @click="open = false" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-200/70 dark:hover:bg-slate-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-auto max-h-[calc(88vh-82px)]">
                <div class="grid gap-4 md:grid-cols-2 mb-5">
                    <div class="rounded-xl border border-blue-200/70 bg-blue-50/70 dark:bg-slate-800 dark:border-slate-700 p-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-blue-700 dark:text-blue-300 mb-2">Cara Menulis Cepat</h4>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">1) Rumus di dalam kalimat: <code class="font-bold">$...$</code></p>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">2) Rumus baris sendiri: <code class="font-bold">$$...$$</code></p>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">3) Untuk pangkat/subskrip banyak karakter gunakan kurung kurawal, contoh <code class="font-bold">x_{12}</code>, <code class="font-bold">a^{n+1}</code>.</p>
                    </div>
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50/70 dark:bg-slate-800 dark:border-slate-700 p-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-amber-700 dark:text-amber-300 mb-2">Error Yang Sering Terjadi</h4>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">- Lupa menutup delimiter: <code class="font-bold">$...$</code> / <code class="font-bold">$$...$$</code>.</p>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">- Menulis <code class="font-bold">x_12</code> padahal harus <code class="font-bold">x_{12}</code>.</p>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">- Copy rumus mentah tanpa delimiter. Solusi: bungkus dengan <code class="font-bold">$...$</code>.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-sm border-collapse">
                        <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-slate-700">
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Kategori</th>
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Bentuk</th>
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Contoh</th>
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Cara Tulis</th>
                            <th class="py-2 font-semibold text-slate-700 dark:text-slate-200">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="row in rows" :key="row.category + row.label">
                            <tr class="border-b border-gray-100 dark:border-slate-800 align-top">
                                <td class="py-3 pr-3 font-semibold text-primary" x-text="row.category"></td>
                                <td class="py-3 pr-3 text-xs font-bold text-slate-600 dark:text-slate-300" x-text="row.label"></td>
                                <td class="py-3 pr-3 text-slate-700 dark:text-slate-200">
                                    <!-- Always show raw text immediately; then upgrade to KaTeX once scripts are ready -->
                                    <div class="rounded-lg bg-blue-50 dark:bg-slate-800 px-3 py-2"
                                         data-latex-example="1"
                                         :data-preview="row.preview"
                                         x-text="row.preview"></div>
                                </td>
                                <td class="py-3 pr-3">
                                    <code class="text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded break-all" x-text="row.snippet"></code>
                                </td>
                                <td class="py-3">
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary hover:bg-primary/20 transition"
                                            @click="insert(row.snippet)">
                                        Sisipkan
                                    </button>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
    <style>
        /* Keep the cheat-sheet table compact and consistent (no big display-math blocks). */
        [data-latex-example="1"] .katex-display { margin: 0 !important; display: inline-block !important; }
        [data-latex-example="1"] .katex { font-size: 1em; }
    </style>
    @push('scripts')
        <script>
            window.__latexRows = [
                { category: 'Dasar', label: 'Inline', preview: '$x+y$', snippet: '$x+y$' },
                // Preview renders inline to keep row height consistent; snippet keeps true display-math.
                { category: 'Dasar', label: 'Display', preview: '$\\displaystyle E=mc^2$', snippet: '$$E=mc^2$$' },
                { category: 'Dasar', label: 'Kurung', preview: '$\\left( a+b \\right)^2$', snippet: '$\\left( a+b \\right)^2$' },
                { category: 'Operasi', label: 'Pecahan', preview: '$\\frac{a}{b}$', snippet: '$\\frac{a}{b}$' },
                { category: 'Operasi', label: 'Akar', preview: '$\\sqrt{x}$', snippet: '$\\sqrt{x}$' },
                { category: 'Operasi', label: 'Akar n', preview: '$\\sqrt[3]{x}$', snippet: '$\\sqrt[3]{x}$' },
                { category: 'Operasi', label: 'Pangkat', preview: '$x^n$', snippet: '$x^n$' },
                { category: 'Operasi', label: 'Subskrip', preview: '$H_2O$', snippet: '$H_2O$' },
                { category: 'Aljabar', label: 'Rumus Jarak', preview: '$S = \\sqrt{(x_{2}-x_{1})^{2} + (y_{2}-y_{1})^{2}}$', snippet: '$S = \\sqrt{(x_{2}-x_{1})^{2} + (y_{2}-y_{1})^{2}}$' },
                { category: 'Kalkulus', label: 'Integral', preview: '$\\int_a^b f(x)\\,dx$', snippet: '$\\int_a^b f(x)\\,dx$' },
                { category: 'Kalkulus', label: 'Limit', preview: '$\\lim_{x \\to 0} \\frac{\\sin x}{x}$', snippet: '$\\lim_{x \\to 0} \\frac{\\sin x}{x}$' },
                { category: 'Deret', label: 'Sigma', preview: '$\\sum_{i=1}^{n} i^2$', snippet: '$\\sum_{i=1}^{n} i^2$' },
                { category: 'Matriks', label: '2x2', preview: '$\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}$', snippet: '$\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}$' },
                { category: 'Simbol', label: 'Yunani', preview: '$\\alpha, \\beta, \\pi$', snippet: '$\\alpha + \\beta = \\pi$' },
                { category: 'Simbol', label: 'Operator', preview: '$\\times, \\div, \\pm$', snippet: '$a \\times b \\pm c$' },
            ];

            window.__latexActiveInput = null;

            window.__renderKatexText = function (element, text) {
                if (!element) return;
                const value = (text || '').trim();
                element.textContent = value;

                // Prefer direct KaTeX render for predictable preview rendering.
                if (window.katex && typeof window.katex.render === 'function') {
                    const isDisplay = value.startsWith('$$') && value.endsWith('$$');
                    const isInline = value.startsWith('$') && value.endsWith('$');

                    if (isDisplay || isInline) {
                        const expr = value.slice(isDisplay ? 2 : 1, isDisplay ? -2 : -1);
                        try {
                            window.katex.render(expr, element, {
                                throwOnError: false,
                                displayMode: isDisplay,
                            });
                            return;
                        } catch (e) {
                            // Fallback below.
                        }
                    }
                }

                // Fallback to auto-render if available.
                if (typeof renderMathInElement === 'function') {
                    renderMathInElement(element, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                        ],
                        throwOnError: false
                    });
                }
            };

            window.setLatexActiveInput = function (element) {
                if (element && element.dataset && element.dataset.latexEnabled === '1') {
                    window.__latexActiveInput = element;
                }
            };

            window.__insertSnippetIntoEditable = function (target, snippet) {
                if (!target || target.getAttribute('contenteditable') !== 'true') {
                    return false;
                }

                target.focus();
                const sel = window.getSelection();
                if (!sel) return false;

                let range;
                if (sel.rangeCount > 0) {
                    range = sel.getRangeAt(0);
                } else {
                    range = document.createRange();
                    range.selectNodeContents(target);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }

                if (!target.contains(range.commonAncestorContainer)) {
                    range = document.createRange();
                    range.selectNodeContents(target);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }

                // Try native insertion first, fallback to manual range insertion.
                let inserted = false;
                try {
                    inserted = document.execCommand('insertText', false, snippet);
                } catch (e) {
                    inserted = false;
                }

                if (!inserted) {
                    range = sel.getRangeAt(0);
                    range.deleteContents();
                    const textNode = document.createTextNode(snippet);
                    range.insertNode(textNode);
                    range.setStartAfter(textNode);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }

                target.dispatchEvent(new Event('input', { bubbles: true }));
                target.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            };

            window.applyRichTextTag = function (tagName) {
                const target = window.__latexActiveInput;
                if (!target || !target.value || typeof target.selectionStart !== 'number' || typeof target.selectionEnd !== 'number') {
                    return;
                }

                const start = target.selectionStart;
                const end = target.selectionEnd;
                const value = target.value ?? '';
                const selected = value.slice(start, end);
                const openTag = `<${tagName}>`;
                const closeTag = `</${tagName}>`;
                const wrapped = `${openTag}${selected || 'teks'}${closeTag}`;

                target.value = value.slice(0, start) + wrapped + value.slice(end);

                const cursorStart = start + openTag.length;
                const cursorEnd = cursorStart + (selected || 'teks').length;
                target.setSelectionRange?.(cursorStart, cursorEnd);

                target.dispatchEvent(new Event('input', { bubbles: true }));
                target.dispatchEvent(new Event('change', { bubbles: true }));
                target.focus();
            };

            if (!window.__latexFocusHandlerBound) {
                window.__latexFocusHandlerBound = true;
                document.addEventListener('focusin', (event) => {
                    window.setLatexActiveInput(event.target);
                });
            }

            window.latexGuideModal = function () {
                return {
                    open: false,
                    rows: window.__latexRows,
                    openAndRender() {
                        this.open = true;
                        this.$nextTick(() => this.renderAllExamples());
                    },
                    renderAllExamples() {
                        const root = this.$root;
                        if (!root) return;

                        const nodes = root.querySelectorAll('[data-latex-example="1"]');
                        nodes.forEach((el) => this.renderExample(el));
                    },
                    renderExample(el, attempt = 0) {
                        if (!el) return;

                        const text = el.dataset.preview || el.textContent || '';
                        // Always set raw content first (never blank)
                        el.textContent = text;

                        // Retry until KaTeX core or auto-render is ready.
                        if (
                            !(window.katex && typeof window.katex.render === 'function') &&
                            typeof window.renderMathInElement !== 'function'
                        ) {
                            if (attempt < 100) {
                                setTimeout(() => this.renderExample(el, attempt + 1), 100);
                            }
                            return;
                        }

                        window.__renderKatexText(el, text);
                    },
                    insert(snippet) {
                        const target = window.__latexActiveInput;
                        if (!target) return;

                        if (!(window.__insertSnippetIntoEditable(target, snippet))) {
                            const start = target.selectionStart ?? target.value.length;
                            const end = target.selectionEnd ?? target.value.length;
                            const value = target.value ?? '';
                            target.value = value.slice(0, start) + snippet + value.slice(end);
                            const cursor = start + snippet.length;
                            target.setSelectionRange?.(cursor, cursor);
                            target.dispatchEvent(new Event('input', { bubbles: true }));
                            target.dispatchEvent(new Event('change', { bubbles: true }));
                            target.focus();
                        }

                        this.open = false;
                    }
                };
            };

            window.latexPreview = function (initialText) {
                return {
                    content: initialText || '',
                    init() {
                        this.$nextTick(() => window.__renderKatexText(this.$refs.preview, this.content));
                    },
                    update(newValue) {
                        this.content = newValue || '';
                        window.__renderKatexText(this.$refs.preview, this.content);
                    }
                };
            };

        </script>
    @endpush
@endonce
