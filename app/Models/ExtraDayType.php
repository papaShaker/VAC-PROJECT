<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraDayType extends Model
{
    use HasFactory;

    protected $table = 'extra_day_types';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
