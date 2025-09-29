<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'member_id',
        'registered_at'
    ];

    protected $casts = [
        'registered_at' => 'datetime'
    ];

    /**
     * Get the event for this registration
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Events::class);
    }

    /**
     * Get the member for this registration
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
