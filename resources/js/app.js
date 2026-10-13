import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ── Rupiah formatter ─────────────────────────────────────────
window.rupiah = (amount) => {
    return 'Rp ' + Number(amount).toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

// ── Dark mode toggle ──────────────────────────────────────────
document.documentElement.classList.toggle(
    'dark',
    localStorage.theme === 'dark' ||
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
);

// ── Alpine Stores ─────────────────────────────────────────────
document.addEventListener('alpine:init', () => {

    // Theme store
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.theme = this.dark ? 'dark' : 'light';
        },
    });

    // Toast notifications
    Alpine.store('toast', {
        items: [],
        show(message, type = 'success', duration = 3500) {
            const id = Date.now();
            this.items.push({ id, message, type });
            setTimeout(() => this.dismiss(id), duration);
        },
        dismiss(id) {
            this.items = this.items.filter(t => t.id !== id);
        },
    });

    // Cashier cart (Alpine store for cart state between components)
    Alpine.store('cart', {
        items: [],
        discount: 0,
        discountType: 'nominal', // 'nominal' | 'percent'

        get subtotal() {
            return this.items.reduce((sum, i) => sum + i.price * i.qty, 0);
        },

        get discountAmount() {
            if (this.discountType === 'percent') {
                return Math.round(this.subtotal * this.discount / 100);
            }
            return this.discount;
        },

        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        get count() {
            return this.items.reduce((sum, i) => sum + i.qty, 0);
        },
    });
});

// ── Livewire event listeners ──────────────────────────────────
document.addEventListener('livewire:init', () => {

    // Flash → toast bridge
    Livewire.on('toast', ({ message, type }) => {
        Alpine.store('toast').show(message, type ?? 'success');
    });

    // Print receipt
    Livewire.on('print-receipt', ({ html }) => {
        const w = window.open('', '_blank');
        w.document.write(html);
        w.document.close();
        w.focus();
        setTimeout(() => { w.print(); w.close(); }, 400);
    });
});

// ── Keyboard shortcuts (F2 / F9 / Esc) ───────────────────────
document.addEventListener('keydown', (e) => {
    if (e.key === 'F2') {
        e.preventDefault();
        const search = document.querySelector('[data-search-input]');
        if (search) search.focus();
    }
    if (e.key === 'F9') {
        e.preventDefault();
        Livewire.dispatch('open-payment-dialog');
    }
    if (e.key === 'Escape') {
        Livewire.dispatch('close-dialogs');
    }
});

Alpine.start();
