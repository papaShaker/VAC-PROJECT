<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobRange;

class JobRangeController extends Controller
{
    public function getAll()
    {
        dd(JobRange::with(['contractType'])->get());
        return JobRange::with(['contractType'])->get();
    }
}
