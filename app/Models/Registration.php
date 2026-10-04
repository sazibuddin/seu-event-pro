<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    public const STATUSES = ['confirmed', 'attended', 'cancelled'];

    protected $fillable = ['event_id', 'user_id', 'ticket_code', 'registration_status', 'registered_at'];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Generates a unique ticket code like TCK-892314. */
    public static function generateTicketCode(): string
    {
        do {
            $code = 'TCK-'.random_int(100000, 999999);
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }
}
