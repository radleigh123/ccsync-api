<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Events extends Model
{
    /** @use HasFactory<\Database\Factories\EventsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'venue',
        'event_date',
        'time_from',
        'time_to',
        'registration_start',
        'registration_end',
        'max_participants',
        'status'
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'time_from' => 'datetime:H:i',
        'time_to' => 'datetime:H:i',
        'registered_at' => 'datetime'
    ];

    /**
     * Get members registered for this event
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'event_registrations', 'event_id', 'member_id')
            ->withTimestamps()
            ->withPivot('registered_at');
    }

    /**
     * Get available slots for the event
     */
    public function getAvailableSlotsAttribute(): int
    {
        return $this->max_participants - $this->members()->count();
    }

    /**
     * Check if event is full
     */
    public function getIsFullAttribute(): bool
    {
        return $this->members()->count() >= $this->max_participants;
    }

    /**
     * Check if registration is currently open
     */
    public function getIsRegistrationOpenAttribute(): bool
    {
        $now = now()->toDateString();
        return $this->status === 'open'
            && $now >= $this->registration_start->toDateString()
            && $now <= $this->registration_end->toDateString()
            && $this->available_slots > 0;
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope for open events
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
