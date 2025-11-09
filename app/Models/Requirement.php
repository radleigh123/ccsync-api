<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    /** @use HasFactory<\Database\Factories\RequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'semester_id',
    ];

    protected $with = [
        'semester:id,title',
    ];

    protected $hidden = [
        'semester_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function offerings(): HasMany
    {
        return $this->hasMany(Offering::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
