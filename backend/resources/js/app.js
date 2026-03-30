import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
window.Chart = Chart;

// ต้องกำหนดก่อน Alpine.start()
window.Alpine = Alpine;

// Dark mode store
Alpine.store('theme', {
    dark: false,
    init() {
        const saved = localStorage.getItem('theme');
        this.dark = saved === 'dark' ||
            (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
        this.apply();
    },
    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        this.apply();
    },
    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
    }
});

// User index search component
Alpine.data('userIndex', (initialQuery = '') => ({
    query: initialQuery,
    loading: false,
    searchTimer: null,
    init() {
        this.$watch('query', (value) => {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.search(value), 300);
        });
    },
    search(value) {
        this.loading = true;
        const url = new URL(window.location);
        if (value) url.searchParams.set('search', value);
        else url.searchParams.delete('search');
        history.replaceState(null, '', url.toString());
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newTable = doc.getElementById('users-table');
                const container = this.$refs.usersTable;
                if (newTable && container) container.innerHTML = newTable.innerHTML;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
    }
}));

/** Thai subdistrict autocomplete: fills subdistrict, district, province, postal; locks อำเภอ/จังหวัด/รหัส after pick */
Alpine.data('thaiSubdistrictPicker', (config) => ({
    searchUrl: config.searchUrl,
    query: '',
    open: false,
    loading: false,
    results: [],
    timer: null,
    highlighted: -1,
    pickerLocked: false,
    lastPickedSub: '',
    init() {
        this.$nextTick(() => {
            const sub = this.$refs.subdistrict;
            if (sub?.value) {
                this.query = sub.value;
                this.lastPickedSub = sub.value.trim();
            }
        });
    },
    onSubdistrictInput(e) {
        this.query = e.target.value;
        if (this.pickerLocked && this.query.trim() !== (this.lastPickedSub || '').trim()) {
            this.pickerLocked = false;
        }
        this.onInput();
    },
    unlockPickerFields() {
        this.pickerLocked = false;
    },
    onInput() {
        this.open = true;
        this.highlighted = -1;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.fetchResults(), 280);
    },
    async fetchResults() {
        const q = (this.query || '').trim();
        if (q.length < 2) {
            this.results = [];
            this.loading = false;
            return;
        }
        this.loading = true;
        this.results = [];
        try {
            const url = new URL(this.searchUrl, window.location.origin);
            url.searchParams.set('q', q);
            const res = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const body = await res.json();
            this.results = Array.isArray(body.data) ? body.data : [];
        } catch {
            this.results = [];
        } finally {
            this.loading = false;
        }
    },
    labelLine(item) {
        return [item.t, item.a, item.p, item.z].join(' » ');
    },
    select(item) {
        this.$refs.subdistrict.value = item.t;
        this.$refs.district.value = item.a;
        this.$refs.province.value = item.p;
        this.$refs.postal.value = item.z;
        this.query = item.t;
        this.lastPickedSub = item.t.trim();
        this.pickerLocked = true;
        this.open = false;
        this.results = [];
        this.highlighted = -1;
    },
    onFocus() {
        if (this.results.length) {
            this.open = true;
        }
    },
    onBlurSoon() {
        setTimeout(() => {
            this.open = false;
        }, 180);
    },
    onKeydown(e) {
        if (!this.open || !this.results.length) {
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.highlighted = Math.min(this.highlighted + 1, this.results.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.highlighted = Math.max(this.highlighted - 1, 0);
        } else if (e.key === 'Enter' && this.highlighted >= 0) {
            e.preventDefault();
            this.select(this.results[this.highlighted]);
        } else if (e.key === 'Escape') {
            this.open = false;
        }
    },
}));

// Cascading lookup component for document form fields
window.cascadingLookup = function (source, dependsOnKey, foreignKey, initialValue) {
    return {
        items: [],
        selected: initialValue || '',
        loading: false,
        init() {
            // Find parent field by name pattern form_payload[dependsOnKey]
            const parentEl = document.querySelector(`[name="form_payload[${dependsOnKey}]"]`);
            if (!parentEl) return;

            parentEl.addEventListener('change', () => this.fetchItems(parentEl.value));

            // Load initial if parent has value
            if (parentEl.value) this.fetchItems(parentEl.value);
        },
        async fetchItems(parentValue) {
            if (!parentValue) {
                this.items = [];
                this.selected = '';
                return;
            }
            this.loading = true;
            try {
                const resp = await fetch(`/lookup?source=${encodeURIComponent(source)}&filters[${encodeURIComponent(foreignKey)}]=${encodeURIComponent(parentValue)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                this.items = json.data || [];
                if (!this.items.find(i => String(i.value) === String(this.selected))) {
                    this.selected = '';
                }
            } finally {
                this.loading = false;
            }
        }
    };
};

// ต้อง start หลังสุด
Alpine.start();

// Sync theme store with FOUC
Alpine.store('theme').init();
