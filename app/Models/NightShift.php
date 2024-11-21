<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NightShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'department_id',
        'week_number',
    ];

    // Define the relationship with the WeeklySchedule model
    public function weeklySchedule()
    {
        return $this->belongsTo(WeeklySchedule::class, 'week_number', 'week_number');
    }

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
