<a href="{{ route('events.show', $event) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
    @if ($event->bannerUrl())
        <img src="{{ $event->bannerUrl() }}" alt="{{ $event->title }}" class="h-44 w-full object-cover">
    @else
        <div class="flex h-44 items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-bold text-white/90">
            {{ $event->category }}
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <span class="w-fit rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ $event->category }}</span>
        <h3 class="mt-3 text-lg font-semibold group-hover:text-indigo-600">{{ $event->title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $event->event_date->format('D, M j, Y · g:i A') }}</p>
        <p class="text-sm text-gray-500">{{ $event->location }}</p>

        <div class="mt-auto pt-4 text-sm">
            @if ($event->isFull())
                <span class="font-semibold text-red-600">Fully booked</span>
            @else
                <span class="font-semibold text-green-700">{{ $event->seatsLeft() }} of {{ $event->capacity }} seats left</span>
            @endif
        </div>
    </div>
</a>
