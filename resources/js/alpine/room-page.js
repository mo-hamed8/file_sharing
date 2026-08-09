import Alpine from 'alpinejs';
import { toast } from './toasts';
import { formatSize } from '../format';

Alpine.data('roomPage', (config) => ({
    status: config.initialStatus,
    hydrated: false,
    participants: [],
    files: [],
    participantCount: 0,
    secondsRemaining: 0,
    timer: null,

    init() {
        this.poll();
        this.timer = setInterval(() => this.poll(), config.pollSeconds * 1000);
        window.addEventListener('room:refresh', () => this.poll());
    },

    async poll() {
        try {
            const response = await fetch(config.stateUrl, { headers: { Accept: 'application/json' } });

            if (!response.ok) {
                return;
            }

            const json = await response.json();
            const data = json.data;

            this.status = data.status;
            this.participants = data.participants;
            this.files = data.files;
            this.participantCount = data.participant_count;
            this.secondsRemaining = data.seconds_remaining;
            this.hydrated = true;

            if (this.status !== 'active') {
                clearInterval(this.timer);
                window.location.reload();
            }
        } catch (error) {
            // Network hiccup — the next poll will retry.
        }
    },

    canDelete(file) {
        return config.isHost || file.uploader_id === config.participantId;
    },

    async deleteFile(file) {
        if (!confirm(`Delete "${file.original_name}"? This cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(`${config.filesBaseUrl}/${file.id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
            });

            if (response.ok) {
                toast('File deleted.', 'success');
                this.poll();
            } else {
                toast('Could not delete this file.', 'error');
            }
        } catch (error) {
            toast('Could not delete this file.', 'error');
        }
    },

    destroy() {
        clearInterval(this.timer);
    },

    formatSize,
}));
