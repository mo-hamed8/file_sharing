@props(['seconds'])

<div
    x-data="countdown({{ (int) $seconds }})"
    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow ring-1 ring-gray-200"
>
    <x-heroicon-o-clock class="h-4 w-4 text-brand-600" />
    <span x-text="label"></span>
</div>
