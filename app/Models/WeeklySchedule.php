<?php

namespace App\Models;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklySchedule extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['department_id', 'week_number', 'schedule_data', 'rotation'];

    protected $casts = [
        'schedule_data' => 'array', // This will automatically cast the JSON field to an array
    ];

   /*  protected $fillable = [
        'date',
        'week_number',
        'night_shift_id',
        'user_id',
        'schedule_id',
        'start_time',
        'end_time',
        'modified_by',
        'notes',
        'department_id'
    ]; */
/*
    // Define the relationship with the NightShift model
    public function nightShift()
    {
        return $this->belongsTo(NightShift::class);
    }
 
    // Define the relationship with the User model (for the assigned user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Schedule model
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    } */

    // Define the relationship with the User model (for the user who modified)
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
