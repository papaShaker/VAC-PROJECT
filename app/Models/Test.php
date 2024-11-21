<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = ['text'];
    protected $table = 'test';
    protected $primarykey = 'id';
    protected $timestamps = false;

}
