@extends('layouts.app')

@section('title', 'My Registrations')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-10">
    <h1 class="text-2xl font-bold">My Registrations</h1>
    <p class="mt-1 text-sm text-gray-500">Show your ticket code at the event entrance.</p>

    <div class="mt-6">
        @include('partials.flash')
    </div>

    @if ($registrations->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500">
            You haven't registered for any events yet.
            <a href="{{ route('home') }}" class="font-medium text-indigo-600 hover:underline">Browse events</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($registrations as $registration)
                @php $event = $registration->event; @endphp
                <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('events.show', $event) }}" class="text-lg font-semibold hover:text-indigo-600">{{ $event->title }}</a>
                        <p class="text-sm text-gray-500">{{ $event->event_date->format('D, M j, Y · g:i A') }} · {{ $event->location }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @include('partials.status-badge', ['status' => $registration->registration_status])
                            @if ($event->status === 'cancelled')
                                <span class="text-xs font-medium text-red-600">Event cancelled by organizer</span>
                            @elseif (! $event->isUpcoming())
                                <span class="text-xs text-gray-500">Event has ended</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="rounded-lg border-2 border-dashed px-4 py-2 text-center {{ $registration->registration_status === 'cancelled' ? 'border-gray-200 text-gray-400 line-through' : 'border-indigo-300 text-indigo-700' }}">
                            <p class="text-[10px] uppercase tracking-wide">Ticket</p>
                            <p class="font-mono text-lg font-bold">{{ $registration->ticket_code }}</p>
                        </div>

                        @if ($registration->registration_status === 'confirmed' && $event->isUpcoming())
                            <form method="POST" action="{{ route('registrations.destroy', $event) }}"
                                  onsubmit="return confirm('Cancel your registration for this event?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
