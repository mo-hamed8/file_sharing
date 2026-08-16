<x-layouts.app>
    <div class="mx-auto flex max-w-5xl flex-col items-center px-4 py-16 text-center sm:py-24">
        <h1>TEST for CI/CD</h1>

        <h1 class="max-w-2xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
            Share files instantly with everyone in the room
        </h1>
        <p class="mt-4 max-w-xl text-lg text-gray-600">
            Create a temporary room, share the QR code, and exchange files without accounts or complicated setup.
        </p>

        @if ($errors->any())
            <div class="mt-6 w-full max-w-md rounded-xl bg-danger-50 px-4 py-3 text-left text-sm text-danger-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div x-data="{ showSettings: false }" class="mt-10 w-full max-w-md">
            <form method="POST" action="{{ route('rooms.store') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-200">
                @csrf

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700 active:bg-brand-800">
                    <x-heroicon-o-plus-circle class="h-5 w-5" />
                    Create a Room
                </button>

                <button type="button" @click="showSettings = !showSettings" class="w-full text-sm font-medium text-brand-600 hover:text-brand-700">
                    <span x-show="!showSettings">Show room settings</span>
                    <span x-show="showSettings" x-cloak>Hide room settings</span>
                </button>

                <div x-show="showSettings" x-cloak x-transition class="space-y-4 border-t border-gray-100 pt-4 text-left">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Room name (optional)</label>
                        <input
                            type="text" name="name" value="{{ old('name') }}" maxlength="100" placeholder="e.g. Room 204"
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Room duration</label>
                        <select name="duration_hours" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach (config('rooms.durations') as $hours)
                                <option value="{{ $hours }}" @selected($hours === config('rooms.default_duration'))>
                                    {{ $hours }} hour{{ $hours > 1 ? 's' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox" name="allow_participant_uploads" value="1" checked
                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                        >
                        Allow participants to upload files
                    </label>
                </div>
            </form>

            <div class="mt-6 rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-200">
                <h2 class="text-sm font-semibold text-gray-700">Have a room code?</h2>
                <form method="POST" action="{{ route('join.store') }}" class="mt-3 flex gap-2">
                    @csrf
                    <input
                        type="text" name="code" maxlength="{{ config('rooms.code.length') }}" placeholder="ABC1234"
                        class="w-full rounded-lg border-gray-300 text-center text-sm uppercase tracking-widest focus:border-brand-500 focus:ring-brand-500"
                    >
                    <button type="submit" class="shrink-0 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Join
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-16 grid w-full max-w-4xl gap-6 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-100">
                <x-heroicon-o-qr-code class="h-6 w-6 text-brand-600" />
                <h3 class="mt-3 text-sm font-semibold text-gray-800">1. Create a room</h3>
                <p class="mt-1 text-sm text-gray-500">Get a room code, link, and QR code in one click — no account needed.</p>
            </div>
            <div class="rounded-2xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-100">
                <x-heroicon-o-users class="h-6 w-6 text-brand-600" />
                <h3 class="mt-3 text-sm font-semibold text-gray-800">2. Others join instantly</h3>
                <p class="mt-1 text-sm text-gray-500">Scan the QR code, open the link, or type the code — that's it.</p>
            </div>
            <div class="rounded-2xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-100">
                <x-heroicon-o-cloud-arrow-up class="h-6 w-6 text-brand-600" />
                <h3 class="mt-3 text-sm font-semibold text-gray-800">3. Share files</h3>
                <p class="mt-1 text-sm text-gray-500">Everyone in the room can upload and download until it expires.</p>
            </div>
        </div>

        <p class="mt-12 flex items-center gap-2 text-xs text-gray-400">
            <x-heroicon-o-shield-check class="h-4 w-4" />
            Rooms and files are temporary and are deleted automatically when a room expires.
        </p>
    </div>
</x-layouts.app>
