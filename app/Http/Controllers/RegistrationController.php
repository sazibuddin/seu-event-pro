<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $registrations = $request->user()->registrations()
            ->with('event')
            ->latest('registered_at')
            ->get();

        return view('my-registrations', compact('registrations'));
    }

    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot register for events.');
        }

        // Lock the event row so two people can't grab the last seat at the same time.
        $result = DB::transaction(function () use ($event, $user) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            if (! $event->isOpenForRegistration()) {
                return ['error', 'Registration is closed for this event.'];
            }

            $existing = $event->registrations()->where('user_id', $user->id)->first();

            if ($existing && $existing->registration_status !== 'cancelled') {
                return ['error', 'You are already registered for this event.'];
            }

            if ($event->isFull()) {
                return ['error', 'Sorry, this event is fully booked.'];
            }

            if ($existing) {
                // Re-register after a cancellation: reuse the row (unique user/event index).
                $existing->update([
                    'registration_status' => 'confirmed',
                    'ticket_code' => Registration::generateTicketCode(),
                    'registered_at' => now(),
                ]);
                $ticket = $existing->ticket_code;
            } else {
                $ticket = $event->registrations()->create([
                    'user_id' => $user->id,
                    'ticket_code' => Registration::generateTicketCode(),
                    'registration_status' => 'confirmed',
                    'registered_at' => now(),
                ])->ticket_code;
            }

            return ['success', 'You are registered! Your ticket code is '.$ticket.'.'];
        });

        return back()->with($result[0], $result[1]);
    }

    public function destroy(Request $request, Event $event)
    {
        $registration = $event->registrations()
            ->where('user_id', $request->user()->id)
            ->where('registration_status', 'confirmed')
            ->first();

        if (! $registration) {
            return back()->with('error', 'You have no active registration to cancel.');
        }

        $registration->update(['registration_status' => 'cancelled']);

        return back()->with('success', 'Your registration has been cancelled.');
    }
}
