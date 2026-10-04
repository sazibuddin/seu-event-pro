@extends('layouts.admin')

@section('title', 'Attendees')

@section('content')
<div class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <div>
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to events</a>
        <h2 class="mt-1 text-xl font-bold">{{ $event->title }}</h2>
        <p class="text-sm text-gray-500">{{ $event->event_date->format('l, M j, Y · g:i A') }} · {{ $event->location }}</p>
    </div>
    <div class="text-left sm:text-right">
        <p class="text-sm text-gray-500">Seats filled</p>
        <p class="text-2xl font-bold">{{ $event->seatsTaken() }} <span class="text-base font-normal text-gray-500">/ {{ $event->capacity }}</span></p>
    </div>
</div>

<form method="GET" action="{{ route('admin.events.attendees', $event) }}" class="mb-4 flex flex-wrap gap-2">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email or ticket code..."
           class="w-full max-w-sm rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Search</button>
    @if (request('search'))
        <a href="{{ route('admin.events.attendees', $event) }}" class="px-2 py-2 text-sm text-gray-500 hover:text-gray-800">Reset</a>
    @endif
</form>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Attendee</th>
                <th class="px-4 py-3">Phone</th>
                <th class="px-4 py-3">Ticket</th>
                <th class="px-4 py-3">Registered</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Update status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($registrations as $registration)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $registrations->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $registration->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $registration->user->email }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $registration->user->phone ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono">{{ $registration->ticket_code }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $registration->registered_at->format('M j, Y g:i A') }}</td>
                    <td class="px-4 py-3">@include('partials.status-badge', ['status' => $registration->registration_status])</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.events.attendees.update', [$event, $registration]) }}" class="inline-flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="registration_status" class="rounded-md border border-gray-300 px-2 py-1 text-sm">
                                @foreach (\App\Models\Registration::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($registration->registration_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1 text-sm font-medium text-white hover:bg-indigo-700">Save</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                        {{ request('search') ? 'No attendees match your search.' : 'No one has registered for this event yet.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $registrations->links() }}</div>
@endsection
