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

use function PHPUnit\Framework\isEmpty;

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


    public function getDailyAvailableUsers($week_number, $department_id, $rotation_index = null, $year = null)
    {
        if(!$year){
            $year = now()->year;
        }
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

            /* 
                        if($department_id === '6'){
            $available_users = $users
            ->reject(fn($user) => $user['USU_nomPerfil'] !== 'booking')->values()
            ->map(fn($user) => collect($user)->except(['job_ranges', 'holidays', 'holiday_zone'])->all()); 
            } else {
                $available_users = $users->map(function ($user) {
                    return collect($user)->except(['holidays', 'USU_zoneFestius', 'job_ranges'])->all();
                });
            }
            */

            $available_users_by_day[$date_string] = [
                'users' => $available_users,
                'count' => $available_users->count(),
                'day_of_week' => $day_of_week
            ];

            $day_of_week++;
        }
        return $this->getWeeklySchedule($available_users_by_day, $users, $department_id, $rotation_index);
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
    
    private function getWeeklySchedule($available_users_by_day, $users, $department_id, $rotation_index = null, $passed_schedules = null) {
        $set_week = [];
        $programmed_users = [];
        $programmed_schedules = [];
        $users_schedule_index_counter = 0;
        $availability = null;
        $available_users_limit_index = $users->count() - 1;
        $add_rows = null;

        /* 
                if($department_id === '6'){
            $users = $users
            ->reject(fn($user) => $user['USU_nomPerfil'] !== 'booking')->values();
        }
        */

        $users_array = $users->toArray();

        $exists = $users->contains(fn($user) => $user['USU_codi'] === 9); //CHECKS IF FIXED USER IS IN ARRAY

        if($rotation_index >= $available_users_limit_index){
            $rotation_index = 0;
        }

        foreach ($users_array as $index => $element) {
            if ($element['id'] == 9 || $element['id'] == 8) {
                $element_to_move = $element;
                unset($users_array[$index]);
                array_unshift($users_array, $element_to_move);
                break;
            }
        }

        if($rotation_index > 0) {
            for ($i = 0; $i < $rotation_index; $i++) {
                $element_to_move = array_pop($users_array); // Take the last element
                array_splice($users_array, 1, 0, [$element_to_move]); // Move it to the 1st index
            }
        }

        // After weekly schedule mapping, increase rotation_index by 1
        $next_rotation_index = $rotation_index + 1;
        if ($next_rotation_index > $available_users_limit_index) {
            $next_rotation_index = 0; // Reset to 0 if it exceeds the user limit
        }

        $users = collect($users_array);
        foreach($users as $user_index => $user_element) {
            $user_week = []; //Has a property for each column of the table it will be represented in
            $schedule_week = [];

            foreach($available_users_by_day as $date => $data) {
                $day_of_week = $data['day_of_week'];
                $count = $data['count'];
                $data['users'] = is_array($data['users']) ? $data['users'] : $data['users']->toArray(); //Ensure $data['users'] is an array.
                
                foreach($data['users'] as $index => $element) { //Loop to make sure these 2 users are always asigned the same schedules.
                    if ($element['id'] == 9 || $element['id'] == 8) {
                        $element_to_move = $element;
                        unset($data['users'][$index]);
                        array_unshift($data['users'], $element_to_move);
                        break;
                    } 
                }
                
                foreach($data['users'] as $index => $user_in_array) {
                    if($user_in_array['id'] == $user_element['id']) {
                        $user = new \stdClass;
                        $user->date = $date;
                        $user->id = $user_element['id'];
                        $user->name = $user_element['name'];
                        $user->is_non_working_day = $user_in_array['is_non_working_day'];
                        $user->is_holiday = $user_in_array['is_holiday'];
                        $user->is_not_available = 0;
                        $user->is_night_shift = 0;
                        $user->holiday_state = $user_in_array['holiday_state'];
                        $user->is_fixed = ($user_element['id'] == 9 || $user_element['id'] == 8) ? 1 : 0;
                        $user_week[] = $user;
                        break;
                    }
                }
                
                $programmed_users[$user_index] = $user_week;

                if ($passed_schedules === null) {
                    if (!$exists) {
                        $count++;
                    }
                    // Retrieve availability and schedules only once per date
                    $availability = $availability ?? UserAvailability::where('department_id', $department_id)
                    ->where('users_available', $count)
                    ->first();

                    if(!$availability) {
                        $availability = UserAvailability::where('department_id', $department_id)->orderBy('users_available', 'desc')->first();
                        $add_rows = $count - $availability->users_available;
                    }

                    $schedules = Schedule::where('day_of_week', $data['day_of_week'])
                    ->where('user_availability_id', $availability->id)
                    ->get();

                    if(!$exists){
                        $schedules = $schedules->reject(fn($schedule) => $schedule['user_group'] === 0)->values();
                    }

                    if($add_rows){
                        for ($i = 0; $i < $add_rows; $i++){
                            $row_to_add =clone $schedules[0];
                            $row_to_add->start_time = "00:00:00";
                            $row_to_add->end_time = "00:00:00";
                            $schedules[] = $row_to_add;
                        }
                    }

                    $schedules_array = $schedules->toArray();
                    
                    if($rotation_index > 0) {
                        for ($i = 0; $i < $rotation_index; $i++) {
                            $element_to_move = array_pop($schedules_array); // Take the last element
                            array_splice($schedules_array, 1, 0, [$element_to_move]); // Move it to the 1st index
                        }
                    }

                    foreach($data['users'] as $index => $user_in_array) {
                        if($user_in_array['id'] == $user_element['id']) {
                            $day = new \stdClass;
                            $day->date = $date;
                            $day->day_of_week = $day_of_week;
                            $day->is_weekend_day = $schedules_array[$index]['is_free_day'];
                            $day->start_time = $this->formattedTime($schedules_array[$index]['start_time']);
                            $day->end_time = $this->formattedTime($schedules_array[$index]['end_time']);
                            $schedule_week[] = $day;
                            /*                         $identifier = 'user_' . $user_element->id;
                                                        $parentObject->$identifier = $userObject; */
                        }
                        
                    }
                    $programmed_schedules[$user_index] = $schedule_week;
                }
                
            }
            
            if($users_schedule_index_counter < $count){
                $users_schedule_index_counter++;
            }
            $user_week = [];
            $schedule_week = [];
        }
        $set_week['users'] = $programmed_users;
        $set_week['schedules'] = ($passed_schedules === null) ? $programmed_schedules : $passed_schedules;
        return ['set_week' => $set_week, 'rotation_index' => $next_rotation_index];
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
    
        // Initialize an array to store week numbers and years
        $week_numbers = [];
    
        // Initialize the current year
        $current_year = $year;
    
        // Loop through all days in the month
        for ($day = 1; $day <= $days_in_month; $day++) {
            $current_date = Carbon::create($year, $month, $day);
            $week_number = $current_date->weekOfYear;  // Get the week number for the current day
    
            // If the week number is 1 and the previous week number was 52, increment the year
            if ($week_number == 1 && !empty($week_numbers) && end($week_numbers)['week'] == 52) {
                $current_year++;
            }
    
            // Add the week number and year to the array (avoid duplicates)
            if (!in_array(['week' => $week_number, 'year' => $current_year], $week_numbers)) {
                $week_numbers[] = ['week' => $week_number, 'year' => $current_year];
            }
        }
        return $week_numbers;
    }
    
    public function weeklySchedule($week_number = null, $department_id = null, $year = null){

        $rotation_index = 0;
        $previous_week_number = null;

        if ($week_number && $week_number === 1) {
            $previous_week_number = 52;
        } else {
            $previous_week_number = $week_number - 1;
        }

        // Check if a record with the same department_id and week_number exists
        $weekly_schedule = WeeklySchedule::where('department_id', $department_id)
            ->where('year', $year)
            ->where('week_number', $week_number)
            ->first();
            
            $previous_week = WeeklySchedule::where('department_id', $department_id)
            ->where('year', $year)
            ->where('week_number', $previous_week_number)
            ->first();

        
        if($previous_week && $previous_week->rotation !== 0) {
            $rotation_index = $previous_week->rotation;
        }
        
        if ($weekly_schedule){

            $data = [
                'weekly_schedule' => $weekly_schedule,
            ];
            return $data;
        } else {
                $schedule_data = $this->getDailyAvailableUsers($week_number, $department_id, $rotation_index, $year); // ROTATION INDEX MUST BE PASSED HERE
                $response = WeeklyScheduleController::saveWeeklySchedule($week_number, $department_id, $schedule_data, $year);
                return $response;
            }
        }


    public function getWeeklySchedulesForMonth ($department_id, $year, $month) {
        $weekly_schedules_for_month = [];
        $weeks = $this->getWeekNumbersForMonth($year, $month);
        foreach ($weeks as $week_index => $week) {
            $weekly_schedule = $this->weeklySchedule($week['week'], $department_id, $week['year']); /* ROTATION INDEX/WEEK MUST BE INITIALIZED IN weeklySchedule FUNCTION */
            $weekly_schedules_for_month[] = $weekly_schedule;
        }
        return $weekly_schedules_for_month;
    }


    public function checkForScheduleTemplate($department_id, $users_available) {
        $schedule_template = UserAvailability::where('department_id', $department_id)
            ->where('users_available', $users_available)
            ->first();

        if (empty($schedule_template)) {
            return ['status' => 'Error', 'message' => 'No hay plantilla disponible para estos parámetros.'];
        }

        $user_availability_id = $schedule_template->id;

        $schedules = Schedule::where('user_availability_id', $user_availability_id)
            ->orderBy('user_group')
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('user_group')
            ->map(function ($group) {
                return $group->keyBy('day_of_week')
                    ->map(function ($schedule) {
                        return (object) [
                            'day_of_week' => $schedule->day_of_week,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'is_free_day' => $schedule->is_free_day,
                        ];
                    });
            })
            ->toArray();

        return $schedules;
    }

    public function removeSchedules($department_id, $users_available) {
        $schedule_template = UserAvailability::where('department_id', $department_id)
        ->where('users_available', $users_available)
        ->first();
        if ($schedule_template) {
            Schedule::where('user_availability_id', $schedule_template->id)->delete();
            $schedule_template->delete();
            return ['status' => 'Success', 'message' => 'Plantilla eliminada con éxito.'];
        }
        else {
            return ['status' => 'Error', 'message' => 'Ha surgido un error.'];
        }
    }

    public function addScheduleTemplate(Request $request) {

        $request = request();

        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'users_schedules' => 'required|array',
            'users_schedules.*.user_group' => 'required|integer',
            'users_schedules.*.days' => 'required|array',
            'users_schedules.*.days.*.day_of_week' => 'required|string',
            'users_schedules.*.days.*.start_time' => 'required|date_format:H:i:s',
            'users_schedules.*.days.*.end_time' => 'required|date_format:H:i:s',
            'users_schedules.*.days.*.is_freeday' => 'required|boolean',
        ]);

        $department_id = $data["department_id"];
        $users_available = $data["users_available"];
        $users_schedule = $data["users_schedules"];

        if (is_null($department_id) || is_null($users_available) || is_null($users_schedule)) { // + is_null(rotation)

            return response()->json(['status' => 'Error', 'message' => 'Faltan parámetros.'], 400);
        }

        $user_availability = new UserAvailability();
        $user_availability->department_id = $department_id;
        $user_availability->users_available = $users_available;
        $user_availability->save();
        
        $user_availability_id = $user_availability->id;

        foreach ($users_schedule as $user_index => $user) {
            foreach ($user['schedules'] as $day){
                $schedule = new Schedule();
                $schedule->day_of_week = $day->day_of_week;
                $schedule->start_time = $day->start_time;
                $schedule->end_time = $day->end_time;
                $schedule->is_free_day = $day->is_free_day;
                $schedule->user_availability_id = $user_availability_id;
                $schedule->user_group = $user_index;
                $schedule->save();
            }
        }
        
        return ['status' => 'Success', 'message' => 'Plantilla creada con éxito.'];
    
    }
}
