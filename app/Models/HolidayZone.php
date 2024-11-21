<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayZone extends Model
{
    use HasFactory;

    protected $table = 'holiday_zones';
    public $timestamps = false;
    protected $primarykey = 'id';

}
