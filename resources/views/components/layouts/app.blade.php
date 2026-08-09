@props(['title' => null])
<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "{$title} — Numas Share" : 'Numas Share' }}</title>
    <meta name="description" content="Share files instantly with everyone in the room.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans text-gray-900 antialiased">
    <div
        x-data="toasts"
        class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4"
    >
        <template x-for="item in items" :key="item.id">
            <div
                class="pointer-events-auto flex max-w-sm items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="{
                    'bg-success-600': item.type === 'success',
                    'bg-danger-600': item.type === 'error',
                    'bg-warning-600': item.type === 'warning',
                }"
                x-text="item.message"
                x-transition
            ></div>
        </template>
    </div>

    <main class="min-h-full">
        {{ $slot }}
    </main>
</body>
</html>
