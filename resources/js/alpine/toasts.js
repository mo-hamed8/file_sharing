import Alpine from 'alpinejs';

Alpine.data('toasts', () => ({
    items: [],
    init() {
        window.addEventListener('toast', (event) => {
            const { message, type = 'success' } = event.detail;
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.remove(id), 4000);
        });
    },
    remove(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
}));

export function toast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
}
