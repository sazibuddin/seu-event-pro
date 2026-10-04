<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'events' => Event::count(),
            'upcoming' => Event::where('status', 'published')->where('event_date', '>=', now())->count(),
            'users' => User::where('role', 'user')->count(),
            'registrations' => Registration::where('registration_status', '!=', 'cancelled')->count(),
        ];

        $upcomingEvents = Event::withCount('activeRegistrations')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        $recentRegistrations = Registration::with(['user', 'event'])
            ->latest('registered_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'upcomingEvents', 'recentRegistrations'));
    }
}
