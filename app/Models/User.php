<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // 'role' is intentionally NOT fillable, so nobody can make themselves admin via a form.
    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Events this user organizes (admins). */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /** Events this user registered for, through the registrations table. */
    public function registeredEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'registrations')
            ->withPivot('ticket_code', 'registration_status', 'registered_at')
            ->withTimestamps();
    }
}
