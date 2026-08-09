<x-layouts.app title="Access Denied">
    <div class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-danger-500/10">
            <x-heroicon-o-shield-check class="h-8 w-8 text-danger-600" />
        </div>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Access denied</h1>
        <p class="mt-2 text-sm text-gray-600">
            {{ $exception->getMessage() ?: "You don't have permission to do that." }}
        </p>

        <a href="{{ route('home') }}" class="mt-8 flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700">
            Return home
        </a>
    </div>
</x-layouts.app>
