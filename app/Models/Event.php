<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Workshop', 'Seminar', 'Hackathon', 'Conference', 'Meetup', 'Competition'];

    public const STATUSES = ['draft', 'published', 'cancelled'];

    protected $fillable = [
        'title', 'slug', 'category', 'description', 'location',
        'event_date', 'capacity', 'status', 'banner_image',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'capacity' => 'integer',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /** Registrations that hold a seat (confirmed or attended, not cancelled). */
    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('registration_status', '!=', 'cancelled');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->withPivot('ticket_code', 'registration_status', 'registered_at')
            ->withTimestamps();
    }

    /** Seats taken. Uses active_registrations_count from withCount() when it was loaded. */
    public function seatsTaken(): int
    {
        return $this->active_registrations_count ?? $this->activeRegistrations()->count();
    }

    public function seatsLeft(): int
    {
        return max(0, $this->capacity - $this->seatsTaken());
    }

    public function isFull(): bool
    {
        return $this->seatsLeft() === 0;
    }

    public function isUpcoming(): bool
    {
        return $this->event_date->isFuture();
    }

    public function isOpenForRegistration(): bool
    {
        return $this->status === 'published' && $this->isUpcoming();
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_image ? Storage::disk('public')->url($this->banner_image) : null;
    }

    /** Builds a unique slug from the title, e.g. "laravel-workshop", "laravel-workshop-2". */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
