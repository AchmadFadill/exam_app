
@push('scripts')
<script>
    window.subjectMultiSelect = (config) => ({
        open: false,
        search: '',
        selected: [],
        items: config.items,
        
        init() {
            // Initialize from config (entangle object)
            this.selected = config.selected;
        },

        get filteredItems() {
            if (this.search === '') return this.items;
            return this.items.filter(item => 
                item.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        toggle(id) {
            // Determine if we are working with an array or a proxy
            // Use Alpine.raw to get the underlying array if possible, or just copy it
            let current = this.selected;
            
            // Safety check: ensure it is an array
            if (!Array.isArray(current)) {
                if (current === null || current === undefined) { 
                    this.selected = []; 
                    current = this.selected;
                } else {
                     // Try to convert to array if it's a proxy
                     current = Array.from(JSON.parse(JSON.stringify(current)));
                }
            }

            // Consistent type checking (convert all to strings for comparison)
            const idStr = String(id);
            const index = current.findIndex(item => String(item) === idStr);

            if (index > -1) {
                // Remove
                current.splice(index, 1);
            } else {
                // Add - push as string to match consistency, or number if original was number
                // Let's stick to the type passed in, but ensure uniqueness
                current.push(id);
            }
            
            // Re-assign to trigger reactivity if needed (for entangled props)
            this.selected = current;
        },
        
        isSelected(id) {
            if (!this.selected) return false;
            return this.selected.includes(String(id)) || this.selected.includes(Number(id));
        },
        
        getLabel(id) {
            let item = this.items.find(i => i.id == id);
            return item ? item.name : '';
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('subjectMultiSelect', window.subjectMultiSelect);
    });

    // Handle case where Alpine is already initialized
    if (typeof Alpine !== 'undefined') {
        Alpine.data('subjectMultiSelect', window.subjectMultiSelect);
    }
</script>
@endpush
