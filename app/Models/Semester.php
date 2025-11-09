<?php

namespace App\Models;

use App\Enums\Status;
use Database\Factories\SemesterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    /** @use HasFactory<SemesterFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'date_start',
        'date_end',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'status' => Status::class,
        ];
    }

    public function member(): HasMany
    {
        return $this->hasMany(Member::class);
    }


    public function event(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
