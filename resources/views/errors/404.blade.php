<x-layouts.app title="Room Not Found">
    <div class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
            <x-heroicon-o-x-circle class="h-8 w-8 text-gray-400" />
        </div>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Room not found</h1>
        <p class="mt-2 text-sm text-gray-600">
            That room doesn't exist, or the link you followed is incorrect. Double-check the code and try again.
        </p>

        <div class="mt-8 flex w-full flex-col gap-3 sm:flex-row">
            <a href="{{ route('join.show') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                <x-heroicon-o-qr-code class="h-4 w-4" />
                Join another room
            </a>
            <a href="{{ route('home') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-gray-600 shadow ring-1 ring-gray-200 hover:bg-gray-50">
                <x-heroicon-o-plus-circle class="h-4 w-4" />
                Create a room
            </a>
        </div>
    </div>
</x-layouts.app>
