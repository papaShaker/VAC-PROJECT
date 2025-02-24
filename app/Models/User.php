<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'holiday_zone',
        'admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function department(){
        return $this->hasOne(Department::class, 'id', 'department');
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'user_id', 'id');
    }
    
/*     public function holidayzone(){
        return $this->hasOne(HolidayZone::class, 'id', 'holiday_zone');
    } */

    public function nonworkingdayzone(){
        return $this->hasOne(NonWorkingDayZone::class, 'id', 'holiday_zone');
    }

    public function jobRanges() {
        return $this->hasMany(JobRange::class, 'user_id', 'id');
    }

    public function rolesToArray()
    {
        return [
            'roles' => $this->getRoleNames(),  // Get role names
            'permissions' => $this->getAllPermissions()->pluck('name'),  // Get permission names
        ];
    }
}
