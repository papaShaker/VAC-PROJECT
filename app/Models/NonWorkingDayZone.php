<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonWorkingDayZone extends Model
{
    use HasFactory;

    protected $table = 'non_working_day_zones';
    public $timestamps = false;
    protected $primaryKey = 'id';
}
