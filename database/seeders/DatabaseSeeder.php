<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin account (role is set with forceFill because it is not mass-assignable)
        $admin = User::firstOrNew(['email' => 'admin@techevent.com']);
        $admin->forceFill([
            'name' => 'Site Admin',
            'password' => 'admin12345', // hashed automatically by the 'hashed' cast
            'role' => 'admin',
        ])->save();

        // 2. Demo student account + a few more users
        $student = User::firstOrNew(['email' => 'user@techevent.com']);
        $student->forceFill([
            'name' => 'Demo Student',
            'password' => 'user12345',
            'phone' => '01700000000',
            'role' => 'user',
        ])->save();

        $users = User::factory(8)->create();

        // 3. Demo events
        $events = [
            ['Laravel 12 Hands-on Workshop', 'Workshop', 'CSE Lab 3', 5, 40, 'published'],
            ['AI & Machine Learning Seminar', 'Seminar', 'Main Auditorium', 9, 150, 'published'],
            ['24-Hour Campus Hackathon', 'Hackathon', 'Innovation Hub', 14, 60, 'published'],
            ['Bangladesh Tech Conference 2026', 'Conference', 'International Convention Center', 21, 300, 'published'],
            ['Open Source Contributors Meetup', 'Meetup', 'Room 7B-12', 3, 5, 'published'],
            ['Competitive Programming Contest', 'Competition', 'CSE Lab 1 & 2', 11, 80, 'published'],
            ['Cloud & DevOps Bootcamp', 'Workshop', 'Online (Zoom)', 30, 100, 'draft'],
            ['Cybersecurity Awareness Talk', 'Seminar', 'Room 5A-01', 7, 50, 'cancelled'],
        ];

        foreach ($events as [$title, $category, $location, $days, $capacity, $status]) {
            Event::updateOrCreate(['slug' => Str::slug($title)], [
                'user_id' => $admin->id,
                'title' => $title,
                'category' => $category,
                'description' => "Join us for {$title}!\n\nThis session is designed for students and professionals who want to learn, network and grow. Bring your laptop and your curiosity.\n\nSeats are limited, so register early.",
                'location' => $location,
                'event_date' => now()->addDays($days)->setTime(15, 0),
                'capacity' => $capacity,
                'status' => $status,
            ]);
        }

        // 4. Demo registrations (fills the 5-seat meetup completely so "Fully booked" can be shown)
        $published = Event::where('status', 'published')->get();
        $meetup = $published->firstWhere('title', 'Open Source Contributors Meetup');

        foreach ($users->take(5) as $user) {
            $this->register($meetup, $user);
        }

        foreach ($published->where('id', '!=', $meetup->id) as $event) {
            foreach ($users->random(4) as $user) {
                $this->register($event, $user);
            }
        }

        $this->register($published->firstWhere('category', 'Workshop'), $student);
    }

    private function register(Event $event, User $user): void
    {
        Registration::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            ['ticket_code' => Registration::generateTicketCode(), 'registered_at' => now()->subHours(rand(1, 72))]
        );
    }
}
