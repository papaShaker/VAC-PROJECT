<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobRange;

class JobRangeController extends Controller
{
    public function getAll()
    {
        return JobRange::with(['contract_types'])->get();
    }
}
