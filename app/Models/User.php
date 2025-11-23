<?php

namespace App\Models;

use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[UseResource(UserResource::class)]
#[UserCollection(UserCollection::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use
        HasFactory,
        Notifiable,
        HasApiTokens,
        HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'display_name',
        'email',
        'email_verified',
        'password',
        'firebase_uid',
    ];

    // Spatie
    protected $guard_name = 'web';

    protected $appends = [
        'role_names',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'firebase_uid',
        'remember_token',
        'roles',
        'permissions',
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * The relations to eager load on every query.
     * @var array
     */
    protected $with = [
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /* protected static function booted()
    {
        static::created(function ($user) {
            $user->member()->create([
                'first_name' => $user->first_name ?? 'Unknown FIRST_NAME',
                'last_name' => $user->last_name ?? 'Unknown LAST_NAME',
                'email' => $user->email ?? 'Uknown EMAIL',
                'id_school_number' => $user->id_school_number,
            ]);
        });
    } */

    /**
     * Get the member associated to user.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Member, User>
     */
    public function member()
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Accessor to role names
     * @return \Illuminate\Support\Collection
     */
    public function getRoleNamesAttribute()
    {
        return $this->getRoleNames();
    }

    /**
     * Acessor to permission names
     * @return \Illuminate\Support\Collection
     */
    public function getPermissionNamesAttribute()
    {
        return $this->getAllPermissions()->pluck('name');
    }
}
