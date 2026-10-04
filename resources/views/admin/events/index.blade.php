@extends('layouts.admin')

@section('title', 'Manage Events')

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" action="{{ route('admin.events.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\Event::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Filter</button>
        @if (request('search') || request('status'))
            <a href="{{ route('admin.events.index') }}" class="px-2 py-2 text-sm text-gray-500 hover:text-gray-800">Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">+ Create Event</a>
</div>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">Event</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Seats</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($events as $event)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('events.show', $event) }}" target="_blank" class="font-medium hover:text-indigo-600">{{ $event->title }}</a>
                        <p class="text-xs text-gray-500">{{ $event->location }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $event->category }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $event->event_date->format('M j, Y g:i A') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $event->seatsTaken() }} / {{ $event->capacity }}</td>
                    <td class="px-4 py-3">@include('partials.status-badge', ['status' => $event->status])</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.events.attendees', $event) }}" class="font-medium text-indigo-600 hover:underline">Attendees</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="font-medium text-gray-700 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                  onsubmit="return confirm('Delete this event and all its registrations? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">No events found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $events->links() }}</div>
@endsection
