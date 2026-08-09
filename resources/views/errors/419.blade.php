<x-layouts.app title="Session Expired">
    <div class="mx-auto flex max-w-md flex-col items-center px-4 py-20 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-warning-500/10">
            <x-heroicon-o-clock class="h-8 w-8 text-warning-600" />
        </div>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Your session expired</h1>
        <p class="mt-2 text-sm text-gray-600">
            This page was open for a while. Please go back and try that action again.
        </p>

        <a href="{{ route('home') }}" class="mt-8 flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700">
            Return home
        </a>
    </div>
</x-layouts.app>
