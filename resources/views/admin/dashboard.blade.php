@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['Total Events', $stats['events'], 'text-indigo-600'],
        ['Upcoming (Published)', $stats['upcoming'], 'text-green-600'],
        ['Registered Users', $stats['users'], 'text-purple-600'],
        ['Active Registrations', $stats['registrations'], 'text-orange-600'],
    ] as [$label, $value, $color])
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold {{ $color }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold">Upcoming events</h2>
            <a href="{{ route('admin.events.create') }}" class="text-sm font-medium text-indigo-600 hover:underline">+ New event</a>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($upcomingEvents as $event)
                @php $percent = $event->capacity ? min(100, round($event->seatsTaken() / $event->capacity * 100)) : 0; @endphp
                <li class="px-5 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('admin.events.attendees', $event) }}" class="font-medium hover:text-indigo-600">{{ $event->title }}</a>
                        @include('partials.status-badge', ['status' => $event->status])
                    </div>
                    <p class="text-xs text-gray-500">{{ $event->event_date->format('M j, Y · g:i A') }} · {{ $event->seatsTaken() }}/{{ $event->capacity }} seats</p>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ $percent }}%"></div>
                    </div>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-gray-500">No upcoming events.</li>
            @endforelse
        </ul>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold">Recent registrations</h2>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($recentRegistrations as $registration)
                <li class="flex items-center justify-between gap-3 px-5 py-3">
                    <div>
                        <p class="font-medium">{{ $registration->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $registration->event->title }} · {{ $registration->registered_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono text-sm">{{ $registration->ticket_code }}</p>
                        @include('partials.status-badge', ['status' => $registration->registration_status])
                    </div>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-gray-500">No registrations yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
