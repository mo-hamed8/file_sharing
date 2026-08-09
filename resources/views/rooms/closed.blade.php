<x-layouts.app title="Room Closed">
    <div class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-warning-500/10">
            <x-heroicon-o-clock class="h-8 w-8 text-warning-600" />
        </div>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">
            {{ $isExpired ? 'This room has expired' : 'This room has ended' }}
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            @if ($isExpired)
                Room {{ $room->code }} reached its time limit. Its files are no longer available.
            @else
                The host ended room {{ $room->code }}. Its files are no longer available.
            @endif
        </p>

        <div class="mt-8 flex w-full flex-col gap-3 sm:flex-row">
            <form method="POST" action="{{ route('rooms.store') }}" class="w-full">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                    <x-heroicon-o-plus-circle class="h-4 w-4" />
                    Create a new room
                </button>
            </form>
            <a href="{{ route('home') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-gray-600 shadow ring-1 ring-gray-200 hover:bg-gray-50">
                Return home
            </a>
        </div>
    </div>
</x-layouts.app>
