<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\HolidayStatusType;
use App\Models\JobRange;

class Holiday extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function statusHolidays(){
        return $this->hasOne(HolidayStatusType::class, 'id', 'status_type'); 
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getHours(){
        $date = $this->date;
        $contract_days = JobRange::where('user_id', $this->user_id)
        ->whereDate('start_date', '<=', $date)
        ->whereDate('end_date', '>=', $date)
        ->with('contractType')
        ->first();
        $contract_days = !empty($contract_days) ? $contract_days->contractType->working_hours : 0;
        return ($contract_days);
    }
}
