<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklySchedule;

class WeeklyScheduleController extends Controller
{
   /*  public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'date' => 'required|date',
            'week_number' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'night_shift_id' => 'nullable|exists:night_shifts,id',
            'schedule_id' => 'required|exists:schedules,id',
            'modified_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
    
        // Create a new WeeklySchedule instance
        $weeklySchedule = new WeeklySchedule();
        $weeklySchedule->date = $validatedData['date'];
        $weeklySchedule->week_number = $validatedData['week_number'];
        $weeklySchedule->user_id = $validatedData['user_id'];
        $weeklySchedule->department_id = $validatedData['department_id'];
        $weeklySchedule->start_time = $validatedData['start_time'];
        $weeklySchedule->end_time = $validatedData['end_time'];
        $weeklySchedule->night_shift_id = $validatedData['night_shift_id'] ?? null;
        $weeklySchedule->schedule_id = $validatedData['schedule_id'];
        $weeklySchedule->modified_by = $validatedData['modified_by'] ?? null;
        $weeklySchedule->notes = $validatedData['notes'];
    
        // Save the WeeklySchedule instance to the database
        $weeklySchedule->save();
    
        // Return a response (could be a redirect, JSON response, etc.)
        return response()->json([
            'message' => 'Weekly schedule created successfully',
            'data' => $weeklySchedule,
        ], 201);
    } */

    public static function saveWeeklySchedule($week_number = null, $department_id = null, $schedule_data = null, $year = null, Request $request = null)
    { //dd("AQUÍ");
        
        $request = request(); // Retrieve the current request

        // If no parameters are passed, use the request data
        if ($request->has(['department_id', 'week_number', 'schedule_data', 'rotation'])) {
            //dd($request);
            $data = $request->validate([
                'department_id' => 'required|integer',
                'week_number' => 'required|integer',
                'schedule_data' => 'required|array',
                'rotation' => 'required|integer',
            ]);
            $week_number = $data["week_number"];
            $department_id = $data["department_id"];
            $year = $data["schedule_data"]["year"];
            $schedule_data = $data["schedule_data"]["schedule_data"];
            $rotation = $data["schedule_data"]["rotation"];
        }
        // Validate input data if passed directly (when $request is null)
        if (is_null($department_id) || is_null($week_number) || is_null($schedule_data) || is_null($year)) { // + is_null(rotation)

            return response()->json(['status' => 'Error', 'message' => 'Missing parameters.'], 400);
        }


        // Check if a record with the same department_id and week_number exists
        $weeklySchedule = WeeklySchedule::where('department_id', $department_id)
                                        ->where('week_number', $week_number)
                                        ->where('year', $year)
                                        ->first();

        if ($weeklySchedule) {
            // Update the existing schedule
            $weeklySchedule->update(['schedule_data' => $data["schedule_data"]["schedule_data"]]);

            return response()->json(['status' => 'Success',
                'message' => 'Horario actualizado con éxito.',
                'weekly_schedule' => $weeklySchedule, ]);// Return updated schedule_data
        } else {

            $data = ['department_id' => $department_id,
                'year' => $year,
                'week_number' => $week_number,
                'schedule_data' => $schedule_data["set_week"],
                'rotation' => $schedule_data["rotation_index"]]; //(check if schedule for prev week exists, if so, get the rotation and check )
            // Insert a new schedule

            $newWeeklySchedule = WeeklySchedule::create($data);
            //dd($newWeeklySchedule); //-> ON FIRST TRY ALL MONTHS
            return response()->json(['status' => 'Success', 
                'message' => 'Se ha generado un nuevo horario.', 
                'weekly_schedule' => $newWeeklySchedule, ]);// Return updated schedule_data]);
        }
    }

}
