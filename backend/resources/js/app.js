import './bootstrap';
import Alpine from 'alpinejs';

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

// ต้อง start หลังสุด
Alpine.start();

// Sync theme store with FOUC
Alpine.store('theme').init();
