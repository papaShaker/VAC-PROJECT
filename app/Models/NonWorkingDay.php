<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonWorkingDay extends Model
{
    use HasFactory;

    protected $table = 'non_working_days';
    public $timestamps = false;
    protected $primaryKey = ['day','zone'];
    public $incrementing = false;

    public function getZone(){
        return $this->hasOne('App\Models\NonWorkingDayZone', 'id', 'zone');
    }
}
