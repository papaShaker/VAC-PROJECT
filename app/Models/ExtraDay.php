<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ExtraDayType;
use App\Models\JobRange;

class ExtraDay extends Model
{
    use HasFactory;

    protected $table = 'extra_days';
    public $timestamps = false;
    protected $primarykey = 'id';

    public function extraDayType(){
        return $this->hasOne(ExtraDayType::class, 'id', 'extra_day_type');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function userDepepartment($department_id) {
        return $this->hasOne(User::class, 'id', 'user_id')->whereIn('department_id',$department_id);
    }

    public function getHours(){
        $day_worked = $this->day_worked;
        $contract_days = JobRange::where('user_id', $this->user_id)
        ->whereDate('start_date', '<=', $day_worked)
        ->whereDate('end_date', '>=', $day_worked)
        /* ->whereBetween($this->day_worked, ['start_date', 'end_date']) */
        ->with('contractType')
        ->first();
        $contract_days = empty($contract_days->contractType) ? 0 : $contract_days->contractType->working_hours;
        return ($contract_days * ($this->extraDayType->bonus_percentage / 100));
    }
}

