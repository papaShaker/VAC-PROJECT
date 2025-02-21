<?php

namespace App\Http\Controllers;

use stdClass;
use Exception;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\JobRange;
use App\Models\Department;
use App\Models\NonWorkingDayZone;
use App\Models\ContractType;
use App\Models\HolidaysPaid;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    //
    public function index()
    {
        // Retrieve only ID and NAME from Department
        $departments = Department::select('id', 'name')->get();

        return Inertia::render('VacationManager', [
            'departments' => $departments
        ]);
    }

    public function getRanges($user_id){
        $result = new stdClass();
        $result->contract_types = ContractType::all();
        $result->ranges = JobRange::where('user_id', $user_id)->with('contractType')->orderBy('start_date')->get();
        $result->paid_holidays = HolidaysPaid::where('user_id', $user_id)->where('created_at', '>=', Carbon::now()->firstOfYear()->format('Y-m-d'))->orderBy('created_at')->get();
        return json_encode($result);
    }

    public function addJobRange(Request $request){
        try{
            $jobRange = new JobRange();
            $jobRange->start_date = $request->new_start_date;
            $jobRange->end_date = $request->new_end_date;
            $jobRange->user_id = $request->user_id;
            $jobRange->contract_type = $request->new_contract_type;
            $jobRange->work_on_freedays = (isset($request->new_work_on_freedays) && $request->new_work_on_freedays ? 1 : 0);
            $jobRange->save();

            return ["status" => "Success",
                    "text" => "Contrato añadido con éxito.",
                    "list" => json_decode($this->getRanges($request->user_id))];
        } catch (Exception $exception) {
            return ["status" => "Error",
                    "text" => "Ha surgido un problema durante la inserción del nuevo contrato."];
        }
    }

    public function getDepartments(){
        $departments = Department::select('id', 'name')->get();
        return $departments;
    }

    public function getZones(){
        $zones = NonWorkingDayZone::select('id', 'zone')->get();
        return $zones;
    }
}