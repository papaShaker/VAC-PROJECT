<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobRange;
use App\Models\User;

class JobRangeController extends Controller
{
    public function getAll()
    {
        dd(JobRange::with(['contractType'])->get());
        return JobRange::with(['contractType'])->get();
    }

    public function getUsersJobRanges()
    {
        $startOfYear = now()->startOfYear()->toDateString();
        $endOfYear = now()->endOfYear()->toDateString();

        $job_ranges = JobRange::select('id', 'contract_type', 'start_date', 'end_date', 'user_id', 'work_on_freedays')
            ->with([
                'contractType:id,name,working_hours',
                'userRange:id,name,department',
                'userRange.department:id,name,holidays_per_month'
            ])
            ->where('start_date', '>=', $startOfYear)
            ->where('end_date', '<=', $endOfYear)
            ->get();
            return response()->json($job_ranges);
    }

    public function getUsersJobRangesForDepartment()
    {
        $user_dep = auth()->user()->department;
        $startOfYear = now()->startOfYear()->toDateString();
        $endOfYear = now()->endOfYear()->toDateString();
    
        $job_ranges = JobRange::select('id', 'contract_type', 'start_date', 'end_date', 'user_id', 'work_on_freedays')
            ->with([
                'contractType:id,name,working_hours',
                'userRange:id,name,department',
                'userRange.department:id,name,holidays_per_month'
            ])
            ->where('start_date', '>=', $startOfYear)
            ->where('end_date', '<=', $endOfYear)
            ->whereHas('userRange', function ($query) use ($user_dep) {
                $query->where('department', $user_dep);
            })
            ->get();
    
        return response()->json($job_ranges);
    }
    
}
