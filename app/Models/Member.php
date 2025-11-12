<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'id_school_number',
        'birth_date',
        'enrollment_date',
        'program',
        'year',
        'is_paid',
        'gender',
        'biography',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'gender' => Gender::class,
            'birth_date' => 'date:Y-m-d',
            'enrollment_date' => 'date:Y-m-d',
        ];
    }

    protected $with = [
        'program',
        'user:id,email',
    ];

    protected $hidden = [
        'user_id',
        'semester_id',
    ];

    /**
     * OPTIONAL inverse to User.
     * Get the user associated to the member.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Member>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
        return $this->belongsToMany(Event::class, 'event_registrations', 'member_id', 'event_id')
            ->withTimestamps()
            ->withPivot('registered_at');
    }

    /**
     * Get the semester the member has/is enrolled on.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
