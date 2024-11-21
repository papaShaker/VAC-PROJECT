<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'homerti',
        'vacalia',
        'users_same_day_holidays',
        'holidays_per_month'
    ];

    public function users(){
        return $this->hasMany(User::class, 'department', 'id');
    }
}
