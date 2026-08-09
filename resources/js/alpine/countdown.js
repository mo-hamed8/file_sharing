import Alpine from 'alpinejs';

Alpine.data('countdown', (secondsRemaining) => ({
    seconds: secondsRemaining,
    label: '',
    timer: null,
    init() {
        this.render();
        this.timer = setInterval(() => {
            if (this.seconds > 0) {
                this.seconds -= 1;
            }
            this.render();
            if (this.seconds <= 0) {
                clearInterval(this.timer);
            }
        }, 1000);

        this.$watch('seconds', () => this.render());
    },
    render() {
        const hours = Math.floor(this.seconds / 3600);
        const minutes = Math.floor((this.seconds % 3600) / 60);
        const secs = Math.floor(this.seconds % 60);
        this.label = [hours, minutes, secs].map((value) => String(value).padStart(2, '0')).join(':');
    },
    destroy() {
        clearInterval(this.timer);
    },
}));
