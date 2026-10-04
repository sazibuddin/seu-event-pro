<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminEventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::withCount('activeRegistrations')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('id') // newest created first
            ->paginate(10)
            ->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create', ['event' => new Event(['status' => 'published'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);

        $data['slug'] = Event::uniqueSlug($data['title']);

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('banners', 'public');
        }

        $request->user()->organizedEvents()->create($data);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validateEvent($request, $event);

        if ($data['title'] !== $event->title) {
            $data['slug'] = Event::uniqueSlug($data['title'], $event->id);
        }

        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('banners', 'public');
        }

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        if ($event->banner_image) {
            Storage::disk('public')->delete($event->banner_image);
        }

        // Registrations are removed too (cascadeOnDelete on the foreign key).
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    /** Lists (and searches) the users registered for one event. */
    public function attendees(Request $request, Event $event)
    {
        $event->loadCount('activeRegistrations');

        $registrations = $event->registrations()
            ->with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->search.'%';
                $q->where(function ($q) use ($term) {
                    $q->where('ticket_code', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest('registered_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.attendees', compact('event', 'registrations'));
    }

    /** Admin changes an attendee's status (e.g. marks them as attended at check-in). */
    public function updateAttendee(Request $request, Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === $event->id, 404);

        $data = $request->validate([
            'registration_status' => ['required', Rule::in(Registration::STATUSES)],
        ]);

        // Re-activating a cancelled ticket must not overbook the event.
        if ($registration->registration_status === 'cancelled'
            && $data['registration_status'] !== 'cancelled'
            && $event->isFull()) {
            return back()->with('error', 'Cannot restore this ticket: the event is full.');
        }

        $registration->update($data);

        return back()->with('success', 'Status for '.$registration->ticket_code.' updated.');
    }

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        $minCapacity = $event ? max(1, $event->activeRegistrations()->count()) : 1;

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Event::CATEGORIES)],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            // New events must be in the future; existing ones may be edited after the fact.
            'event_date' => $event ? ['required', 'date'] : ['required', 'date', 'after:now'],
            'capacity' => ['required', 'integer', 'min:'.$minCapacity, 'max:100000'],
            'status' => ['required', Rule::in(Event::STATUSES)],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'capacity.min' => 'Capacity cannot be lower than :min (seats already taken).',
        ]);
    }
}
