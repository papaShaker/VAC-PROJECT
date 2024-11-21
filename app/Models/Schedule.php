<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'user_availability_id',
        'start_time',
        'end_time',
        'is_free_day',
        // other fields as necessary
    ];

    // Relationship with UsersAvailability (Many-to-One)
    public function userAvailability()
    {
        return $this->belongsTo(UsersAvailability::class, 'user_availability_id');
    }
}
