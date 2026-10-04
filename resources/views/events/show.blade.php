@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="mx-auto max-w-5xl px-4 py-10">
    <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; All events</a>

    <div class="mt-4">
        @include('partials.flash')
    </div>

    @if ($event->status === 'cancelled')
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">This event has been cancelled.</div>
    @elseif ($event->status === 'draft')
        <div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-medium text-yellow-800">Draft preview: only admins can see this page.</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if ($event->bannerUrl())
            <img src="{{ $event->bannerUrl() }}" alt="{{ $event->title }}" class="h-64 w-full object-cover sm:h-80">
        @else
            <div class="flex h-48 items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-3xl font-bold text-white/90 sm:h-64">
                {{ $event->category }}
            </div>
        @endif

        <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-3">
            {{-- Details --}}
            <div class="lg:col-span-2">
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{{ $event->category }}</span>
                <h1 class="mt-3 text-3xl font-bold">{{ $event->title }}</h1>

                <dl class="mt-4 space-y-1 text-gray-600">
                    <div><dt class="inline font-medium text-gray-800">Date:</dt> <dd class="inline">{{ $event->event_date->format('l, F j, Y · g:i A') }}</dd></div>
                    <div><dt class="inline font-medium text-gray-800">Location:</dt> <dd class="inline">{{ $event->location }}</dd></div>
                    <div><dt class="inline font-medium text-gray-800">Organizer:</dt> <dd class="inline">{{ $event->organizer->name }}</dd></div>
                </dl>

                <h2 class="mt-8 text-lg font-semibold">About this event</h2>
                {{-- e() escapes HTML first, then nl2br adds line breaks: safe against XSS --}}
                <div class="mt-2 leading-relaxed text-gray-700">{!! nl2br(e($event->description)) !!}</div>
            </div>

            {{-- Seat counter + register box --}}
            <aside class="h-fit rounded-xl border border-gray-200 bg-gray-50 p-6">
                @php
                    $taken = $event->seatsTaken();
                    $percent = $event->capacity > 0 ? min(100, round($taken / $event->capacity * 100)) : 100;
                @endphp

                <p class="text-sm font-medium text-gray-500">Seats</p>
                <p class="mt-1 text-3xl font-bold">{{ $event->seatsLeft() }} <span class="text-base font-normal text-gray-500">left of {{ $event->capacity }}</span></p>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $percent }}%"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ $taken }} registered</p>

                <div class="mt-6">
                    @if ($registration && $registration->registration_status !== 'cancelled')
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-center">
                            <p class="text-sm text-green-800">You're registered!</p>
                            <p class="mt-1 font-mono text-xl font-bold tracking-wider text-green-900">{{ $registration->ticket_code }}</p>
                            <p class="mt-1 text-xs capitalize text-green-700">Status: {{ $registration->registration_status }}</p>
                        </div>

                        @if ($registration->registration_status === 'confirmed' && $event->isUpcoming())
                            <form method="POST" action="{{ route('registrations.destroy', $event) }}" class="mt-3"
                                  onsubmit="return confirm('Cancel your registration for this event?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-red-300 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Cancel registration</button>
                            </form>
                        @endif
                    @elseif (! $event->isOpenForRegistration())
                        <button disabled class="w-full cursor-not-allowed rounded-lg bg-gray-300 py-3 font-semibold text-gray-600">Registration closed</button>
                    @elseif ($event->isFull())
                        <button disabled class="w-full cursor-not-allowed rounded-lg bg-gray-300 py-3 font-semibold text-gray-600">Fully booked</button>
                    @elseif (auth()->user()?->isAdmin())
                        <a href="{{ route('admin.events.attendees', $event) }}" class="block w-full rounded-lg bg-gray-900 py-3 text-center font-semibold text-white hover:bg-gray-800">View attendees (admin)</a>
                    @else
                        @auth
                            <form method="POST" action="{{ route('registrations.store', $event) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700">
                                    {{ $registration ? 'Register again' : 'Register now' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block w-full rounded-lg bg-indigo-600 py-3 text-center font-semibold text-white hover:bg-indigo-700">Log in to book</a>
                        @endauth
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
