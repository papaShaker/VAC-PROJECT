<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidaysPaid extends Model
{
    use HasFactory;

    protected $table = 'holidays_paid';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
