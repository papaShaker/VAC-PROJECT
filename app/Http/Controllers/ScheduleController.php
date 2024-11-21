<?php

namespace App\Http\Controllers;

use stdClass;
use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Holiday;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\NonWorkingDay;
use App\Models\WeeklySchedule;
use App\Models\UserAvailability;
use App\Models\NonWorkingDayZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\WeeklyScheduleController;

class ScheduleController extends Controller
{
    // Display a listing of the weekly schedules
    public function index()
    {
        $weeklySchedules = WeeklySchedule::all(); // You may want to paginate this
        return response()->json($weeklySchedules);
    }

    public function getDepartments()
    {
        // Retrieve only ID and NAME from Department
        $departments = Department::select('id', 'name')->get();
        return Inertia::render('Schedule', [
            'departments' => $departments
        ]);
    }

    // Store a newly created weekly schedule
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'week_number' => 'required|integer|between:1,53', // Assuming a max of 53 weeks in a year
            'night_shift_id' => 'required|exists:night_shifts,id',
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'modified_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create a new weekly schedule
        $weeklySchedule = WeeklySchedule::create($request->only([
            'date',
            'week_number',
            'night_shift_id',
            'user_id',
            'schedule_id',
            'start_time',
            'end_time',
            'modified_by',
            'notes',
            'department_id'
        ]));

        return response()->json($weeklySchedule, 201); // Return the created schedule with a 201 status
    }

    // Update the specified weekly schedule
    public function update(Request $request, $id)
    {
        $weeklySchedule = WeeklySchedule::find($id);

        if (!$weeklySchedule) {
            return response()->json(['message' => 'Weekly schedule not found'], 404);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'week_number' => 'sometimes|required|integer|between:1,53',
            'night_shift_id' => 'sometimes|required|exists:night_shifts,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'schedule_id' => 'sometimes|required|exists:schedules,id',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'modified_by' => 'sometimes|required|exists:users,id',
            'notes' => 'nullable|string',
            'department_id' => 'sometimes|required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Update the weekly schedule
        $weeklySchedule->update($request->only([
            'date',
            'week_number',
            'night_shift_id',
            'user_id',
            'schedule_id',
            'start_time',
            'end_time',
            'modified_by',
            'notes',
            'department_id'
        ]));

        return response()->json($weeklySchedule);
    }

    // Display the specified weekly schedule by week_number and department_id
    public function show(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'week_number' => 'required|integer|between:1,53', // Assuming a maximum of 53 weeks
            'department_id' => 'required|exists:departments,id', // Ensure the department exists
        ]);

        // Retrieve the week_number and department_id from the request
        $weekNumber = $request->input('week_number');
        $departmentId = $request->input('department_id');

        // Query the WeeklySchedule based on week_number and department_id
        $weeklySchedules = WeeklySchedule::where('week_number', $weekNumber)
            ->where('department_id', $departmentId)
            ->get(); // Get all schedules for the specified week and department

        // Check if any schedules were found
        if ($weeklySchedules->isEmpty()) {
            return response()->json(['message' => 'Weekly schedules not found for the specified week and department'], 404);
        }

        return response()->json($weeklySchedules);
    }

    public function calendarDepartment($year = null, $passed_department_id = null, $year_month = null)
    {
        $is_user = Auth::guard('web')->user();
        /*         if (!$is_user || ($is_user->USU_depId != $passed_department_id && $is_user->USU_nomPerfil != 'gestor_central')) {
            return abort(403, 'Acceso no autorizado Yep.');
        } */

        $users = new \stdClass;
        $users->by_department = [];
        $all_holiday_zones = NonWorkingDayZone::all()->keyBy('id'); // Use keyBy for fast access
        $users_by_department = User::with('holidayzone')->where('department', $passed_department_id)->get();

        $users_same_day_holidays = Department::find($passed_department_id)->users_same_day_holidays;

        foreach ($users_by_department as $user) {
            $user_data = (object)[
                'id' => $user->id,
                'name' => $user->name,
                'holiday_zones' => $this->getUserHolidayZones($user->holidayzone, $all_holiday_zones)
            ];
            $users->by_department[$passed_department_id][] = $user_data;
        }

        // Parse the $monthYear format
        if ($year_month) {
            list($year, $month) = explode('-', $year_month);
            $today = empty($year) ? Carbon::now()->firstOfYear() : Carbon::parse($year . '-' . $month . '-01'); // Set to the first day of the specified month
        } else {
            $today = empty($year) ? Carbon::now()->firstOfYear() : Carbon::parse($year . '-01-01')->firstOfYear();
        }

        $calendar = [];
        $non_working_days = NonWorkingDay::where('day', '>=', $today->format('Y-m-d'))
            ->whereIn('zone', $all_holiday_zones->keys())->get();

        $user_holidays_collection = $this->getUserHolidays($users->by_department[$passed_department_id]);

        if (empty($year_month)) {
            // Generate calendar
            for ($i = 0; $i < 13; $i++) {
                if ($i != 0) {
                    $today->addMonth();
                }
                $calendar[] = $this->generateMonthData($today, $non_working_days, $user_holidays_collection);
            }
        } else {
            $calendar[] = $this->generateMonthData($today, $non_working_days, $user_holidays_collection);
        }

        return json_encode($calendar);
    }

    private function getUserHolidayZones($current_zone, $all_holiday_zones)
    {
        $holiday_zones = [];
        while ($current_zone) {
            $holiday_zones[] = $current_zone->id;
            $current_zone = $all_holiday_zones->get($current_zone->parent);
        }
        return $holiday_zones;
    }

    private function getUserHolidays($users_data)
    {
        $user_holidays_collection = new \stdClass();

        foreach ($users_data as $user_data) {
            $user_holidays = Holiday::where('user_id', $user_data->id)
                ->where('status_type', '!=', 3)
                ->with('statusHolidays', 'user')
                ->get();

            $user_holidays_collection->{$user_data->id} = $user_holidays;
        }

        return $user_holidays_collection;
    }


    public function getDailyAvailableUsers($week_number, $department_id)
    {
        $year = now()->year;
        $start_of_week = Carbon::now()->setISODate($year, $week_number)->startOfWeek();
        $end_of_week = $start_of_week->copy()->endOfWeek();

        // Step 1: Get users in the department with active ranges for the entire week
        $users = User::select('id', 'name', 'department', 'holiday_zone', 'is_admin')->where('department', $department_id)
            ->whereHas('jobRanges', function ($query) use ($start_of_week, $end_of_week) {
                $query->where('start_date', '<=', $end_of_week)
                    ->where('end_date', '>=', $start_of_week);
            })->with(['jobRanges' => function ($query) use ($start_of_week, $end_of_week) {
                $query->select('user_id', 'work_on_freedays')
                    ->where('start_date', '<=', $end_of_week)
                    ->where('end_date', '>=', $start_of_week);
            }])->with(['holidays' => function ($query) use ($start_of_week, $end_of_week) {
                $query->select('user_id', 'date', 'status_type')
                ->where('date', '<=', $end_of_week)
                ->where('date', '>=', $start_of_week);
            }])
            ->get();

        $users->each(function ($user) {
            $user->is_work_on_freedays = $user->jobRanges->contains('work_on_freedays', true)? 1 : 0;
        });

        $available_users_by_day = [];
        $day_of_week = 1;

        // Step 2: Loop through each day in the week
        foreach (CarbonPeriod::create($start_of_week, $end_of_week) as $date) {
            $date_string = $date->format('Y-m-d');

             foreach($users as $user) {
                if($this->isNonWorkingDayForUser($date, $user)){
                    $user->is_non_working_day = 1;
                } else {
                    $user->is_non_working_day = 0;
                }
                if ($user->holidays->isNotEmpty()) {
                    foreach ($user->holidays as $key => $value) {
                        if($value->date == $date_string){
                            $user->is_holiday = ($value->date == $date_string) ? 1 : 0;
                            $user->holiday_state = $value-> status_type;
                        } else {
                            $user->is_holiday = 0;
                            $user->holiday_state = 0;
                        }
                    }
                } else {
                    $user->is_holiday = 0;
                    $user->holiday_state = 0;
                }
                
            }

            // Filter out users with non-working days or holidays
            $available_users = $users->map(function ($user) {
                // Exclude the nonworkingdayzone field by converting each user object to an array
                return collect($user)->except(['nonworkingdayzone', 'job_ranges', 'holidays', 'holiday_zone'])->all();
            });

            $counter = $available_users->count();
            $available_users_by_day[$date_string] = [
                'users' => $available_users,
                'count' => $available_users->count(),
                'day_of_week' => $day_of_week
            ];

            $day_of_week++;
        }
        
        return $this->getWeeklySchedule($available_users_by_day, $users, $department_id);;
    }

    //Helper method to see if a user has a free day being part of a NonWorkingDayZone.
    private function isNonWorkingDayForUser($date, $user)
    {
        $currentZone = $user->nonworkingdayzone;

        // Traverse up the zone hierarchy
        while ($currentZone) {
            $nonWorkingDay = NonWorkingDay::where('day', $date)
                ->where('zone', $currentZone->id)
                ->exists();

            if ($nonWorkingDay) {
                return true; // Found a non-working day for this zone or a parent zone
            }

            // Move up the hierarchy
            $currentZone = $currentZone->parent ? NonWorkingDayZone::find($currentZone->parent) : null;
        }

        return false; // No non-working day found in this hierarchy
    }

    private function getWeeklySchedule($available_users_by_day, $users, $department_id) {
        $users_schedule = [];
        $users_schedule_index_counter = 0;
        $availability = null;
        $users_array = $users->toArray();
        foreach($users_array as $index => $element) { //Sets the object so that these 2 users appear at first index (0) in the final weekly schedule object.
            if ($element['id'] == 9) {
                $element_to_move = $element;
                unset($users_array[$index]);
                array_unshift($users_array, $element_to_move);
                break;
            } 
            if ($element['id'] == 8) {
                $element_to_move = $element;
                unset($users_array[$index]);
                array_unshift($users_array, $element_to_move);
                break;
            } 
        }

        $users = collect($users_array);

        foreach($users as $user_element) {
            $user_week = new \stdClass; //Has a property for each column of the table it will be represented in
            $user_week->user = new \stdClass;
            $user_week->user->id = $user_element["id"];
            $user_week->user->name = $user_element["name"];
            foreach($available_users_by_day as $date => $data) {
                foreach($data['users'] as $index => $element) { //Loop to make sure these 2 users are always asigned the same schedules.
                    $data['users'] = is_array($data['users']) ? $data['users'] : $data['users']->toArray(); //Ensure $data['users'] is an array.
                    if ($element['id'] == 9 || $element['id'] == 8) {
                        $element_to_move = $element;
                        unset($data['users'][$index]);
                        array_unshift($data['users'], $element_to_move);
                        break;
                    } 
                }
                $count = $data['count'];
                $day_of_week = $data['day_of_week'];
                if($availability == null) {
                    $availability = UserAvailability::where('department_id', $department_id)->where('users_available', $count)->first();
                }
                $schedules = Schedule::where('day_of_week', $data['day_of_week'])->where('user_availability_id', $availability->id)->get();

                foreach($data['users'] as $index => $user_in_array) {
                    if($user_in_array['id'] == $user_week->user->id) {
                        $day = new \stdClass;
                        $day->date = $date;
                        $day->day_of_week = $day_of_week;
                        $day->is_work_on_freedays = $user_in_array['is_work_on_freedays'];
                        $day->is_non_working_day = $user_in_array['is_non_working_day'];
                        $day->is_holiday = $user_in_array['is_holiday'];
                        $day->is_not_available = 0;
                        $day->is_night_shift = 0;
                        $day->holiday_state = $user_in_array['holiday_state'];
                        $day->is_weekend_day = $schedules[$index]['is_free_day'];
                        $day->start_time = $this->formattedTime($schedules[$index]['start_time']);
                        $day->end_time = $this->formattedTime($schedules[$index]['end_time']);
                        /*                         $identifier = 'user_' . $user_element->id;
                                                    $parentObject->$identifier = $userObject; */
                        $user_week->$day_of_week = $day;
                    }
                    
                }
            }
            $users_schedule[$users_schedule_index_counter] = $user_week;
            if($users_schedule_index_counter < $count){
                $users_schedule_index_counter++;
            }
        }

        return $users_schedule;
    }



        // Helper method to convert the day of the week number (1-7) to a string name
    private function getDayName($dayOfWeek)
    {
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return $days[$dayOfWeek - 1]; // Adjust for 1-based index
    }

    private function formattedTime($time)
    { // Split the time string by `:` and take the first two components (hours and minutes)
    list($hours, $minutes) = explode(':', $time);
    return $hours . ':' . $minutes;
    }

    private function generateMonthData($today, $non_working_days, $user_holidays_collection)
    {
        $period = CarbonPeriod::create($today->copy()->firstOfMonth(), $today->copy()->lastOfMonth());
        $weeks = []; // Initialize an array for weeks
        $currentWeekNumber = null;

        foreach ($period as $date) {
            // Get the week number for the current date
            $weekNumber = $date->isoWeek(); // ISO week number (1-52)

            // If we are in a new week, initialize a new week object
            if ($currentWeekNumber !== $weekNumber) {
                $currentWeekNumber = $weekNumber;
                $weeks[$currentWeekNumber] = new \stdClass();
                $weeks[$currentWeekNumber]->days = []; // Initialize days array for the week
            }

            $date_string = $date->format('Y-m-d');
            $days_data = (object)[
                'dayOfWeek' => $date->dayOfWeek,
                'day' => $date->format('d'),
                'free_day' => in_array($date->dayOfWeek, [6, 0]) || $non_working_days->contains('day', $date_string),
                'holidays' => [],
                'is_holiday' => 0,
                'holiday_info' => 0,
                'is_work_on_freedays' => 0,
                'day_state' => $date->copy()->startOfDay()
                    ->format('Y-m-d') < Carbon::now()->startOfDay()->format('Y-m-d') ? 0 : ($date->startOfDay()->format('Y-m-d') == Carbon::now()->startOfDay()->format('Y-m-d') ? 1 : 2),
                'day_selected' => 0
            ];

            // Collect holidays for this day
            foreach ($user_holidays_collection as $user_holidays) {
                $user_holidays_day = $user_holidays->where('date', $date_string)->first();
                if ($user_holidays_day) {
                    $range = $user_holidays_day->user->jobRanges()->activeOn($user_holidays_day->date)->first();
                    $days_data->holidays[] = (object)[
                        'user_name' => $user_holidays_day->user->name,
                        'user_holiday_status' => $user_holidays_day->status_type,
                        'user_active_range' => $range,
                        'user_work_on_freedays' => $range ? $range->work_on_freedays : null
                    ];
                }
            }

            // Check for holiday status
            $days_data->is_holiday = !empty($days_data->holidays) ? 1 : 0;

            // Aggregate the holiday information
            if (!empty($days_data->holidays)) {
                $statuses = array_column($days_data->holidays, 'user_holiday_status');
                $days_data->holiday_info = count(array_unique($statuses)) >= 2 ? 1 : 0;

                $is_wof = array_column($days_data->holidays, 'user_work_on_freedays');
                $days_data->is_work_on_freedays = count(array_unique($is_wof)) >= 2 ? 1 : 0;
            }

            // Add the day's data to the current week's days
            $weeks[$currentWeekNumber]->days[$date_string] = $days_data;
        }

        Carbon::setLocale('es');

        // Return the month data including weeks
        return (object)[
            'month_name' => $today->translatedFormat('F Y'),
            'previous_month' => $today->copy()->endOfMonth()->startOfDay()->isBefore(Carbon::now()->endOfMonth()->startOfDay()),
            'weeks' => $weeks // Return the weeks structure
        ];
    }

    public function getDatesForWeek($year, $week_number) {
        $dates = [];
        $date =  Carbon::now();
        
        // Create a Carbon instance and set it to the first day of the given week and year
        $date = Carbon::now()->setISODate($year, $week_number, 1); // 1 = Monday

        // Loop through the week and add each day to the array
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $date->format('d-m-Y');
            $date->addDay(); // Move to the next day using Carbon's addDay() method
        }
        return $dates;
    }


    public function getWeekNumbersForMonth($year, $month) {
        // Create a Carbon instance for the first day of the given month
        $start_of_month = Carbon::create($year, $month, 1);

        // Get the total number of days in the month
        $days_in_month = $start_of_month->daysInMonth;

        // Initialize an array to store week numbers
        $week_numbers = [];

        // Loop through all days in the month
        for ($day = 1; $day <= $days_in_month; $day++) {
            $current_date = Carbon::create($year, $month, $day);
            $week_number = $current_date->weekOfYear;  // Get the week number for the current day
            
            // Add the week number to the array (avoid duplicates)
            if (!in_array($week_number, $week_numbers)) {
                $week_numbers[] = $week_number;
            }
        }

        return $week_numbers;
    }

    public function weeklySchedule($week_number = null, $department_id = null, Request $request = null){
            // Check if a record with the same department_id and week_number exists
            $weeklySchedule = WeeklySchedule::where('department_id', $department_id)
                ->where('week_number', $week_number)
                ->first();
        if ($weeklySchedule){
            $data = [
                'weekly_schedule' => $weeklySchedule,
            ];
            return $data;
        } else {
            $schedule_data = $this->getDailyAvailableUsers($week_number, $department_id);
            $response = WeeklyScheduleController::saveWeeklySchedule($week_number, $department_id, $schedule_data);
            return $response;
        }
    }
}
