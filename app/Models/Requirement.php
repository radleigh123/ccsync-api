<?php

namespace App\Models;

use App\Http\Resources\Requirement\RequirementCollection;
use App\Http\Resources\Requirement\RequirementResource;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseResource(RequirementResource::class)]
#[UseResourceCollection(RequirementCollection::class)]
class Requirement extends Model
{
    /** @use HasFactory<\Database\Factories\RequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'semester_id',
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
