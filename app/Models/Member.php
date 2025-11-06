<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'suffix',
        'id_school_number',
        'email',
        'birth_date',
        'enrollment_date',
        'program',
        'year',
        'is_paid',
        'gender',
        'biography',
        'phone',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    /**
     * The attributes that are included to model.
     * @var array
     */
    protected $with = [
        'program',
    ];

    /**
     * Get the program that the member belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Program, Member>
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program', 'code');
    }

    /**
     * Get the events that the member is registered for.
     */
    public function events()
    {
        return $this->belongsToMany(Events::class, 'event_registrations', 'member_id', 'event_id')
            ->withTimestamps()
            ->withPivot('registered_at');
    }

    /**
     * OPTIONAL inverse to User.
     * Get the user associated to the member.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Member>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
