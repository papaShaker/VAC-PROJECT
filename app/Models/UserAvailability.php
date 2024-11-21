<?php

namespace App\Models;

use App\Models\Schedule;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAvailability extends Model
{
    use HasFactory;

    protected $table = 'user_availability';
    protected $fillable = [
        'users_available',
        'department_id',
    ];

     // Relationship with Department
     public function department()
     {
         return $this->belongsTo(Department::class);
     }
 
     // Relationship with Schedule (Many-to-Many)
     public function schedules()
     {
         return $this->belongsToMany(Schedule::class, 'user_availability_schedules', 'user_availability_id', 'schedule_id');
     }
}
