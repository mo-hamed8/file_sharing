@props(['uploadUrl'])

<div x-data="uploadDropzone(@js($uploadUrl), @js(csrf_token()))" class="space-y-4">
    <div
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop($event)"
        class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-10 text-center transition"
        :class="dragging ? 'border-brand-500 bg-brand-50' : 'border-gray-300 bg-white'"
    >
        <x-heroicon-o-cloud-arrow-up class="h-10 w-10 text-brand-500" />
        <div>
            <p class="text-sm font-medium text-gray-700">Drag and drop files here</p>
            <p class="text-xs text-gray-500">or</p>
        </div>
        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 active:bg-brand-800">
            <x-heroicon-o-plus-circle class="h-5 w-5" />
            Select files
            <input type="file" class="hidden" multiple @change="onSelect($event)">
        </label>
    </div>

    <ul class="space-y-2" x-show="uploads.length > 0" x-cloak>
        <template x-for="upload in uploads" :key="upload.id">
            <li class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-800" x-text="upload.name"></p>
                        <p class="text-xs text-gray-500" x-text="formatSize(upload.size)"></p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <x-heroicon-o-check-circle x-show="upload.status === 'done'" class="h-5 w-5 text-success-600" />
                        <x-heroicon-o-x-circle x-show="upload.status === 'failed'" class="h-5 w-5 text-danger-600" />
                        <button type="button" x-show="upload.status === 'uploading'" @click="cancel(upload)" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                        <button type="button" x-show="upload.status !== 'uploading'" @click="remove(upload)" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                </div>
                <div x-show="upload.status === 'uploading'" class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-brand-500 transition-all" :style="`width: ${upload.progress}%`"></div>
                </div>
                <p x-show="upload.status === 'failed'" class="mt-1 text-xs text-danger-600" x-text="upload.error"></p>
            </li>
        </template>
    </ul>
</div>
