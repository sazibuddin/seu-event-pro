<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /** Home page: upcoming published events with search + category filter. */
    public function index(Request $request)
    {
        $events = Event::withCount('activeRegistrations')
            ->where('status', 'published')
            ->where('event_date', '>=', now())
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->orderBy('event_date')
            ->paginate(9)
            ->withQueryString();

        return view('home', [
            'events' => $events,
            'categories' => Event::CATEGORIES,
        ]);
    }

    public function show(Request $request, Event $event)
    {
        // Drafts are only visible to admins.
        abort_if($event->status === 'draft' && ! $request->user()?->isAdmin(), 404);

        $event->loadCount('activeRegistrations');

        $registration = $request->user()
            ? $event->registrations()->where('user_id', $request->user()->id)->first()
            : null;

        return view('events.show', compact('event', 'registration'));
    }
}
