<x-layouts.app title="Join a Room">
    <div class="mx-auto flex max-w-md flex-col items-center px-4 py-16">
        <x-heroicon-o-qr-code class="h-10 w-10 text-brand-600" />
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Join a Room</h1>
        <p class="mt-2 text-center text-sm text-gray-600">Enter the room code you were given.</p>

        @if ($errors->any())
            <div class="mt-6 w-full rounded-xl bg-danger-50 px-4 py-3 text-sm text-danger-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('join.store') }}" class="mt-6 w-full space-y-4 rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-200">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Room code</label>
                <input
                    type="text" name="code" autofocus maxlength="{{ config('rooms.code.length') }}" value="{{ old('code') }}"
                    placeholder="ABC1234"
                    class="mt-1 w-full rounded-lg border-gray-300 text-center text-lg uppercase tracking-[0.3em] focus:border-brand-500 focus:ring-brand-500"
                >
            </div>
            <button type="submit" class="w-full rounded-full bg-brand-600 px-6 py-3 text-base font-semibold text-white hover:bg-brand-700">
                Continue
            </button>
        </form>

        <a href="{{ route('home') }}" class="mt-6 text-sm font-medium text-brand-600 hover:text-brand-700">
            &larr; Back to home
        </a>
    </div>
</x-layouts.app>
