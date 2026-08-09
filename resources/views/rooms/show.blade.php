@php
    $roomConfig = [
        'stateUrl' => route('rooms.state', $room),
        'filesBaseUrl' => url("/rooms/{$room->public_id}/files"),
        'initialStatus' => $room->status->value,
        'pollSeconds' => config('rooms.polling_interval_seconds'),
        'isHost' => $actor->isHost(),
        'participantId' => $actor->participant?->public_id,
        'csrfToken' => csrf_token(),
    ];
@endphp

<x-layouts.app :title="$room->name ?: $room->code">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:py-12" x-data="roomPage(@js($roomConfig))">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">Room</p>
                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                        Active
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $room->name ?: 'Room '.$room->code }}</h1>
                <p class="mt-1 font-mono text-sm text-gray-500">Code: {{ $room->code }}</p>
            </div>

            <div class="flex items-center gap-3">
                <x-ui.countdown :seconds="now()->diffInSeconds($room->expires_at)" />
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 rounded-full bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow ring-1 ring-gray-200 hover:bg-gray-50">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                    Exit
                </a>
            </div>
        </div>

        @if ($actor->isStranger())
            {{-- Join form --}}
            <div class="mx-auto mt-10 max-w-md rounded-2xl bg-white p-6 text-center shadow-lg ring-1 ring-gray-200">
                <x-heroicon-o-user-group class="mx-auto h-10 w-10 text-brand-600" />
                <h2 class="mt-3 text-lg font-semibold text-gray-900">Join this room</h2>
                <p class="mt-1 text-sm text-gray-500">Enter a display name to see and share files in this room.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl bg-danger-50 px-4 py-3 text-left text-sm text-danger-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('rooms.join', $room) }}" class="mt-5 space-y-3 text-left">
                    @csrf
                    <input
                        type="text" name="display_name" maxlength="50" required autofocus placeholder="e.g. Sara"
                        value="{{ old('display_name') }}"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                    <button type="submit" class="w-full rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                        Join Room
                    </button>
                </form>
            </div>
        @else
            {{-- Dashboard --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <x-room.qr-code :room="$room" />

                    <div class="rounded-2xl bg-white p-5 shadow ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Shareable link</p>
                        <div x-data="{ copied: false, link: '{{ route('rooms.short', $room) }}' }" class="mt-2 flex items-center gap-2">
                            <input type="text" readonly :value="link" @click="$el.select()" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600">
                            <button
                                type="button"
                                @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 2000)"
                                class="shrink-0 rounded-lg bg-gray-900 p-2.5 text-white hover:bg-gray-700"
                            >
                                <x-heroicon-o-clipboard class="h-4 w-4" x-show="!copied" />
                                <x-heroicon-o-check-circle class="h-4 w-4" x-show="copied" x-cloak />
                            </button>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow ring-1 ring-gray-200">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Participants</p>
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-gray-700">
                                <x-heroicon-o-users class="h-4 w-4" />
                                <span x-text="participantCount"></span>
                            </span>
                        </div>

                        <template x-if="!hydrated">
                            <div class="mt-3 space-y-2">
                                <div class="h-8 animate-pulse rounded-lg bg-gray-100"></div>
                                <div class="h-8 animate-pulse rounded-lg bg-gray-100"></div>
                            </div>
                        </template>

                        <ul class="mt-3 space-y-2" x-show="hydrated">
                            <template x-for="participant in participants" :key="participant.id">
                                <li class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700" x-text="participant.display_name.charAt(0).toUpperCase()"></span>
                                    <span class="truncate text-sm text-gray-700" x-text="participant.display_name"></span>
                                    <span x-show="participant.role === 'host'" class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">Host</span>
                                </li>
                            </template>
                        </ul>

                        <template x-if="hydrated && participants.length === 0">
                            <p class="mt-3 text-sm text-gray-400">No one has joined yet.</p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        @if ($actor->isHost())
                            <form method="POST" action="{{ route('rooms.end', $room) }}"
                                @submit="if (! confirm('End this room for everyone? This cannot be undone.')) $event.preventDefault()">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-danger-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-danger-700">
                                    <x-heroicon-o-power class="h-4 w-4" />
                                    End room
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('rooms.leave', $room) }}"
                                @submit="if (! confirm('Leave this room?')) $event.preventDefault()">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow ring-1 ring-gray-200 hover:bg-gray-50">
                                    <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" />
                                    Leave room
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    @if ($room->allow_participant_uploads || $actor->isHost())
                        <x-room.upload-dropzone :upload-url="route('rooms.files.store', $room)" />
                    @else
                        <div class="rounded-2xl bg-white p-5 text-center text-sm text-gray-500 shadow ring-1 ring-gray-200">
                            <x-heroicon-o-x-circle class="mx-auto h-6 w-6 text-gray-400" />
                            <p class="mt-2">The host has disabled uploads from participants.</p>
                        </div>
                    @endif

                    <div class="rounded-2xl bg-white p-5 shadow ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Shared files</p>

                        <template x-if="!hydrated">
                            <div class="mt-3 space-y-2">
                                <div class="h-14 animate-pulse rounded-lg bg-gray-100"></div>
                                <div class="h-14 animate-pulse rounded-lg bg-gray-100"></div>
                            </div>
                        </template>

                        <template x-if="hydrated && files.length === 0">
                            <div class="mt-6 text-center text-sm text-gray-400">
                                <x-heroicon-o-inbox class="mx-auto h-8 w-8 text-gray-300" />
                                <p class="mt-2">No files shared yet. Be the first to upload one.</p>
                            </div>
                        </template>

                        <ul class="mt-3 divide-y divide-gray-100" x-show="hydrated">
                            <template x-for="file in files" :key="file.id">
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-800" x-text="file.original_name"></p>
                                        <p class="text-xs text-gray-500">
                                            <span x-text="file.uploader_name"></span> ·
                                            <span x-text="new Date(file.uploaded_at).toLocaleTimeString()"></span> ·
                                            <span x-text="formatSize(file.size)"></span>
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a :href="file.download_url" class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                                            <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5" />
                                            Download
                                        </a>
                                        <button
                                            type="button"
                                            x-show="canDelete(file)"
                                            @click="deleteFile(file)"
                                            class="rounded-full p-1.5 text-gray-400 hover:bg-danger-50 hover:text-danger-600"
                                        >
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
