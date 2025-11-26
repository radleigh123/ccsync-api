<?php

namespace App\Models;

use App\Http\Resources\Requirement\OfferingCollection;
use App\Http\Resources\Requirement\OfferingResource;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseResource(OfferingResource::class)]
#[UseResourceCollection(OfferingCollection::class)]
class Offering extends Model
{
    protected $fillable = [
        'requirement_id',
        'semester_id',
        'open_at',
        'close_at',
        'max_submissions',
        'active',
    ];

    protected $hidden = [
        'requirement_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function compliances(): HasMany
    {
        return $this->hasMany(Compliance::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
