<div x-data="latexGuideModal()" @open-latex-guide.window="open = true">
    <div x-cloak x-show="open" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-4xl max-h-[88vh] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/70">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Panduan Rumus</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Klik contoh untuk menyisipkan ke kolom yang sedang aktif.</p>
                </div>
                <button type="button" @click="open = false" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-200/70 dark:hover:bg-slate-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-auto max-h-[calc(88vh-82px)]">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm border-collapse">
                        <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-slate-700">
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Kategori</th>
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Contoh</th>
                            <th class="py-2 pr-3 font-semibold text-slate-700 dark:text-slate-200">Snippet</th>
                            <th class="py-2 font-semibold text-slate-700 dark:text-slate-200">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="row in rows" :key="row.category + row.label">
                            <tr class="border-b border-gray-100 dark:border-slate-800 align-top">
                                <td class="py-3 pr-3 font-semibold text-primary" x-text="row.category"></td>
                                <td class="py-3 pr-3 text-slate-700 dark:text-slate-200">
                                    <div class="rounded-lg bg-blue-50 dark:bg-slate-800 px-3 py-2" x-init="window.__renderKatexText($el, row.preview)"></div>
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
    @push('scripts')
        <script>
            window.__latexRows = [
                { category: 'Dasar', label: 'Inline', preview: '$x+y$', snippet: '$x+y$' },
                { category: 'Dasar', label: 'Display', preview: '$$E=mc^2$$', snippet: '$$E=mc^2$$' },
                { category: 'Operasi', label: 'Pecahan', preview: '$\\frac{a}{b}$', snippet: '$\\frac{a}{b}$' },
                { category: 'Operasi', label: 'Akar', preview: '$\\sqrt{x}$', snippet: '$\\sqrt{x}$' },
                { category: 'Operasi', label: 'Pangkat', preview: '$x^n$', snippet: '$x^n$' },
                { category: 'Operasi', label: 'Subskrip', preview: '$H_2O$', snippet: '$H_2O$' },
                { category: 'Simbol', label: 'Yunani', preview: '$\\alpha, \\beta, \\pi$', snippet: '$\\alpha + \\beta = \\pi$' },
                { category: 'Simbol', label: 'Operator', preview: '$\\times, \\div, \\pm$', snippet: '$a \\times b \\pm c$' },
            ];

            window.__latexActiveInput = null;

            window.__renderKatexText = function (element, text) {
                if (!element) return;
                element.textContent = text || '';
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
                    insert(snippet) {
                        const target = window.__latexActiveInput;
                        if (!target) return;

                        const start = target.selectionStart ?? target.value.length;
                        const end = target.selectionEnd ?? target.value.length;
                        const value = target.value ?? '';
                        target.value = value.slice(0, start) + snippet + value.slice(end);
                        const cursor = start + snippet.length;
                        target.setSelectionRange?.(cursor, cursor);
                        target.dispatchEvent(new Event('input', { bubbles: true }));
                        target.dispatchEvent(new Event('change', { bubbles: true }));
                        target.focus();
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
