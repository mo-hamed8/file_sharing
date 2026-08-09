@props(['room'])

<div class="flex flex-col items-center gap-3 rounded-2xl bg-white p-5 shadow ring-1 ring-gray-200">
    <img
        src="{{ route('rooms.qr', $room) }}"
        alt="QR code to join this room"
        class="h-40 w-40 rounded-lg"
        width="160"
        height="160"
    >
    <a
        href="{{ route('rooms.qr', $room) }}"
        download="numas-share-{{ $room->code }}.png"
        class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700"
    >
        <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
        Download QR
    </a>
</div>
