import Alpine from 'alpinejs';
import { toast } from './toasts';
import { formatSize } from '../format';

Alpine.data('uploadDropzone', (uploadUrl, csrfToken) => ({
    dragging: false,
    uploads: [],

    onDrop(event) {
        this.dragging = false;
        this.handleFiles(event.dataTransfer.files);
    },

    onSelect(event) {
        this.handleFiles(event.target.files);
        event.target.value = '';
    },

    handleFiles(fileList) {
        Array.from(fileList).forEach((file) => this.uploadFile(file));
    },

    uploadFile(file) {
        const entry = Alpine.reactive({
            id: `${Date.now()}-${Math.random()}`,
            name: file.name,
            size: file.size,
            progress: 0,
            status: 'uploading',
            error: null,
            xhr: null,
        });

        this.uploads.unshift(entry);

        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        entry.xhr = xhr;

        xhr.open('POST', uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                entry.progress = Math.round((event.loaded / event.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                entry.status = 'done';
                entry.progress = 100;
                toast(`${file.name} uploaded successfully.`, 'success');
                window.dispatchEvent(new CustomEvent('room:refresh'));
            } else {
                entry.status = 'failed';
                entry.error = this.parseErrorMessage(xhr);
                toast(entry.error, 'error');
            }
        });

        xhr.addEventListener('error', () => {
            entry.status = 'failed';
            entry.error = 'Network error during upload.';
            toast(entry.error, 'error');
        });

        xhr.addEventListener('abort', () => {
            entry.status = 'cancelled';
        });

        xhr.send(formData);
    },

    cancel(entry) {
        entry.xhr?.abort();
    },

    remove(entry) {
        this.uploads = this.uploads.filter((upload) => upload.id !== entry.id);
    },

    parseErrorMessage(xhr) {
        try {
            const json = JSON.parse(xhr.responseText);

            if (json.errors) {
                return Object.values(json.errors).flat()[0] ?? json.message;
            }

            return json.message || 'Upload failed.';
        } catch (error) {
            return 'Upload failed.';
        }
    },

    formatSize,
}));
