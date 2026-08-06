@props(['links' => []])

<aside class="w-56 shrink-0 border-r border-gray-200 bg-white">
    <div class="px-4 py-4 text-lg font-semibold text-gray-900">PsyCare</div>

    <nav class="space-y-1 px-3">
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                class="block rounded-md px-3 py-2 text-sm font-medium {{ ($link['active'] ?? false) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}"
            >
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
