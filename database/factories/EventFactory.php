<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->catchPhrase();

        return [
            'user_id' => User::factory()->admin(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'category' => fake()->randomElement(Event::CATEGORIES),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->randomElement(['Auditorium A', 'Room 7B-12', 'Online (Zoom)', 'Main Hall', 'CSE Lab 3']),
            'event_date' => fake()->dateTimeBetween('+3 days', '+2 months'),
            'capacity' => fake()->numberBetween(20, 150),
            'status' => 'published',
        ];
    }
}
