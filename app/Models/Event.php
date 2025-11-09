<?php

namespace App\Models;

use App\Enums\Status;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
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
        'event_date' => 'date:Y-m-d',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'time_from' => 'datetime',
        'time_to' => 'datetime',
        'registered_at' => 'datetime',
        'status' => Status::class,
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

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
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
        $now = now();
        return $this->status === Status::OPEN
            && $now->gte(Carbon::parse($this->registration_start))
            && $now->lte(Carbon::parse($this->registration_end))
            && $this->available_slots > 0;
    }

    public function getRegistrationDueAttribute(): int
    {
        $now = now();
        $registrationEnd = Carbon::parse($this->registration_end);
        $daysRemaining = $now->diffInDays($registrationEnd);
        if ($daysRemaining < 0) return -1;
        return $daysRemaining;
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope for events on current month
     */
    public function scopeThisMonth($query)
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        return $query->whereBetween('event_date', [$startOfMonth, $endOfMonth])->whereYear('event_date', now()->year);
    }

    /**
     * Scope for open events
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
