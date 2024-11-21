<?php

namespace App\Models;

use App\Models\ContractType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobRange extends Model
{
    use HasFactory;

    protected $table = 'job_ranges';
    public $timestamps = false;
    protected $primarykey = 'id';

    public function contractType(){
        return $this->hasOne(ContractType::class, 'id', 'contract_type');
    }

    public function userRange(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeActiveOn($query, $date) {
        return $query->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }
}
