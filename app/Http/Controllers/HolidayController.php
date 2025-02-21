<?php

namespace App\Http\Controllers;

use Throwable;
use App\Constant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ExtraDay;
use App\Models\ExtraDayType;
use App\Models\Holiday;
use App\Models\HolidayStatusType;
use App\Models\HolidaysPaid;
use App\Models\ContractType;
use App\Models\JobRange;
use App\Models\Department;
use App\Models\NonWorkingDay;
use App\Models\NonWorkingDayZone;
use \Illuminate\Validation\ValidationException;
use App\Mail\NotifyUserEmail;
use Error;
use Exception;
use Illuminate\Support\Facades\Mail;

use function Laravel\Prompts\error;
use function PHPUnit\Framework\throwException;

class HolidayController extends Controller
{
    //
    public $DAYS_PER_MONTH = 2;

    public function index($year = null, $user_id = null, $search_accumulated_holidays = true)
    {
        
        if (empty($user_id) || $user_id === null) {
            $user_id = Auth::guard('web')->user()->id;
            $user = Auth::guard('web')->user();
        } else {
            $user = User::find($user_id);
        }
        /* ALL DATA FOR A USER */

        //Stores the row/register/department where id of Departments table is equal to the current user's department_id.
        //Then it changes the DAYS_PER_MONTH according to that department.
        $department = Department::where('id', $user->department)->first();

        $this->DAYS_PER_MONTH = $department->holidays_per_month;

        if ($year == null) $year = Carbon::now()->year; //If year NOT asigned, year == current year.
        //$today will be [The current date if $year matches current year] / [A Carbon date object representing Jenuary 1st of the $year, if this doesnt match the current year.]
        $today = $year == Carbon::now()->year ? Carbon::now() : Carbon::parse($year . '-01-01');
        //Selects the specific contract where contract's user_id == current user's id donde
        $job_ranges = JobRange::where('user_id', $user_id)
            ->where('start_date', 'like', $today->copy()->firstOfYear()->format('Y') . '%')
/*             ->where('end_date', '>', $today->copy()->firstOfYear()->format('Y-m-d')) */
            ->with('contractType')
            ->get();

        $current_working_hours = 8; //Sets a default work hours/day in case no contract is found to callback.
        $hours = 0;

        foreach ($job_ranges as $range) {
            $start_date = Carbon::parse($range->start_date)->startOfDay();
            $end_date = Carbon::parse($range->end_date)->endOfDay();

            if (empty($range->contractType)) {
                $range->contractType->working_hours = 0;
            }
            if ($today >= $start_date && $today <= $end_date) {
                // If contract type exists, use its working hours \ floatval($range->contractType->working_hours)
                $current_working_hours = $range->contractType->working_hours;
            } else {
                $current_working_hours = 8; // Reset to default if not in range
            }

            $start_month = $start_date->copy()->firstOfMonth();
            $end_month = $end_date->copy()->lastOfMonth()->endOfDay()->addSeconds(1);
            $total_days = $start_month->diffInDays($end_month);

            $end_date->addSeconds(1);
            $days = $start_date->diffInDays($end_date);
            $months = $start_date->diffInMonths($end_date, false);

            if ($total_days != $days) {
                $months++; //Rounding up to include possible partial months in the calculation.
                $hours += ($days * ($months * $range->contractType->working_hours * $this->DAYS_PER_MONTH)) / $total_days;
            } else {
                $hours += $months * $range->contractType->working_hours * $this->DAYS_PER_MONTH;
            }
        }

        //It retrieves the extra days that have been worked.
        $extra_days = ExtraDay::where('user_id', $user_id)
            ->where('day_worked', 'like', $today->copy()->firstOfYear()->format('Y') . '%')
            ->with('extraDayType')
            ->get();

        //It sums
        $hours_per_extra_days = 0;
        foreach ($extra_days as $extra_day) {
            $hours_per_extra_days += $extra_day->getHours();
        }

        $holidays_spent = Holiday::where('user_id', $user_id)
            ->where('date', '>=', $today->copy()->startOfYear()->format('Y-m-d'))
            ->where('date', '<=', $today->copy()->endOfYear()->addMonth()->format('Y-m-d') . '%')
            ->where('year_request', $year)
            ->with('statusHolidays')
            ->get();

        $pending_holiday_hours = 0;
        $confirmed_holiday_hours = 0;
        $enjoyed_holiday_hours = 0;
        //For each Holidays_spent entry it checks whether its status_type is pending,
        //confirmed, or confirmed plus past current date, which means its been enjoyed already.
        foreach ($holidays_spent as $holidays) {
            $holidays->status_type == 1 ? $pending_holiday_hours += $holidays->getHours() : false;
            $holidays->status_type == 2 ? $confirmed_holiday_hours += $holidays->getHours() : false;
            $holidays->status_type == 2 && Carbon::parse($holidays->date) < Carbon::now() ? $enjoyed_holiday_hours += $holidays->getHours() : false;
        }

        $paid_holidays_days = 0;
        $holidays_paid = HolidaysPaid::where('user_id', $user_id)
            ->where('created_at', 'like', '%' . $year . '%')
            ->get();
        foreach ($holidays_paid as $holiday_paid) {
            $paid_holidays_days += $holiday_paid->days;
        }

        /* USER'S MACRO OBJECT */
        $object = new \stdClass();
/*         $object->accumulated_hours_previous_year = 0; */
        //$previous_year_pending_holidays is gonna be = to Index for prevous year, it calculates the hours to be requested(not enjoyed) and it saves them inside that variable.
        //If there's any holiday hours not enjoyed previous years it saves them inside accumulated_hours_previous_year, else accumulated_hours_previous_year is gonna be 0.
        if ($search_accumulated_holidays && $year > 2024) {
            $prevous_year_pending_holidays = json_decode($this->index(($year - 1), $user_id, true))->holiday_hours_to_be_requested;
            $object->accumulated_hours_previous_year = $prevous_year_pending_holidays < 0 ? 0 : $prevous_year_pending_holidays;
        } else {
            $object->accumulated_hours_previous_year = 0;
        }
        
        
        /* Building up the object with data related to user's holidays status. */
        $object->hours_per_contract = $hours;
        $object->hours_per_extra_days = $hours_per_extra_days;
        $object->pending_holiday_hours_to_be_confirmed = $pending_holiday_hours;
        $object->confirmed_holiday_hours = $confirmed_holiday_hours;
        $object->enjoyed_holiday_hours = $enjoyed_holiday_hours;
        $object->pending_holiday_hours_to_be_enjoyed = ($confirmed_holiday_hours - $enjoyed_holiday_hours);
        $object->paid_holidays_days = $paid_holidays_days;
        $object->accumulated_holidays_days = round($object->accumulated_hours_previous_year / $current_working_hours, 0, PHP_ROUND_HALF_DOWN);
        $object->pending_holidays_hours_to_be_requested = ($object->hours_per_contract == 0 ? 0 : round($object->accumulated_hours_previous_year + $object->hours_per_contract - $object->pending_holiday_hours_to_be_confirmed - $object->confirmed_holiday_hours) / $current_working_hours);
        $object->extra_holidays_days = $object->hours_per_extra_days == 0 ? 0 : round($object->hours_per_extra_days / $current_working_hours, 1);
        $object->pending_holidays_total_to_be_requested = round($object->pending_holidays_hours_to_be_requested, 0, PHP_ROUND_HALF_DOWN) + $object->extra_holidays_days - $paid_holidays_days;
        $object->enjoyed_holidays_days = $object->enjoyed_holiday_hours == 0 ? 0 : round($object->enjoyed_holiday_hours / $current_working_hours, 0, PHP_ROUND_HALF_DOWN);
        $object->holiday_hours_to_be_requested = (($hours + $hours_per_extra_days + $object->accumulated_hours_previous_year) - $confirmed_holiday_hours - $pending_holiday_hours - ($current_working_hours * $paid_holidays_days)); 
        $object->user = $user;
        $object->current_working_hours = $current_working_hours;
        return json_encode($object);
    }

    public function calendar($year = null, $passed_user_id = null)
    {
        $user = Auth::guard('web')->user();
        $user_id = empty($passed_user_id) ? $user->id : $passed_user_id;
        $users_same_day_holidays = Department::find($user->department)->users_same_day_holidays;


        $user_holiday_zone = User::where('id', $user_id)->with('nonworkingdayzone')->first();
        $all_holiday_zones = NonWorkingDayZone::select('id');
        $holiday_zones_list = [];
        
        if ($user_holiday_zone && $user_holiday_zone->nonworkingdayzone) {
            // Start with the user's own holiday zone
            $holiday_zones_list[] = $user_holiday_zone->nonworkingdayzone->id;
            
            // Traverse up the hierarchy
            $parent_zone_search = $user_holiday_zone->nonworkingdayzone->parent;
            
            while ($parent_zone_search != null) {
                $parent_zone = NonWorkingDayZone::where('id', $parent_zone_search)->first();
                
                if (!$parent_zone) {
                    break; // Prevent infinite loop if parent ID is invalid
                }
                
                $holiday_zones_list[] = $parent_zone->id;
                $parent_zone_search = $parent_zone->parent; // Update for next iteration
            }
        }


        $users_department_holidays = [];
        if ($users_department_holidays > 0) {
            $user_department = User::where('department', $user->department);
            $users_department_holidays = Holiday::join('users', 'users.id', 'holidays.user_id')
                ->where('date', '>=', Carbon::now()->format('Y-m-d'))->get();
        }

        $today = empty($year) ? Carbon::now()->firstOfYear() : Carbon::parse($year . '-01-01')->firstOfYear();


        $calendar = [];
        $months_count = 13;
        $months_names = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $user_holidays = Holiday::where('user_id', $user_id)
            ->where('status_type', '!=', 3)->with('statusHolidays')->get();
        $non_working_days = NonWorkingDay::where('day', '>=', $today->copy()->firstOfYear()->format('Y-m-d'))
            ->whereIn('zone', $all_holiday_zones)->get(); /* HERE whereIn */
        $job_ranges = JobRange::where('user_id', $user_id)
            ->with('contractType')->get();

        /* Generates a calendar for a specific number of months (13), itirating through each one and filling in the details about each day. */
        //Outer loop runs for the number of months.
        for ($i = 0; $i < $months_count; $i++) {
            if ($i != 0) { //Skips the first iteration.
                $today->addMonth(); //Increments the month of $today.
            }
            $period = CarbonPeriod::create($today->copy()->firstOfMonth(), $today->copy()->lastOfMonth()); //$period represents the days of the month.
            $month = [];
            $adjust = true;

            /* Initializes entries in $month[] for days that precede the firs day of the current month within the context of a calendar layout.
               The $date->dayOfWeek method of the Carbon date object returns a numeric representation of the day of the week (0 for Sunday, 1 for Monday... 6 for Saturday*/
            foreach ($period as $date) {
                if ($date->dayOfWeek != 1 && $adjust) {
                    for ($y = 1; $y < ($date->dayOfWeek == 0 ? 7 : $date->dayOfWeek); $y++) {
                        $month[$y] = new \stdClass();
                    }
                }
                /*  */
                $adjust = false;
                /*  */
                $month[$date->format('Y-m-d')] = new \stdClass();
                /*  */
                $month[$date->format('Y-m-d')]->dayOfWeek = $date->dayOfWeek;
                $month[$date->format('Y-m-d')]->day = $date->format('d');
                //Sets the day/date as free_day if its a Saturday or Sunday, or if it's a non working day.
                $month[$date->format('Y-m-d')]->free_day = \in_array($date->dayOfWeek, [6, 0]) || count($non_working_days->where('day', $date->format('Y-m-d')));
                $user_holidays_day = $user_holidays->where('date', $date->format('Y-m-d'))->first();
                $month[$date->format('Y-m-d')]->holiday = isset($user_holidays_day->status_type) ? $user_holidays_day->status_type : 0;
                $month[$date->format('Y-m-d')]->holiday_info = isset($user_holidays_day->status_type) ? $user_holidays_day : false;
                //day_state gives us information about the date, if it has passed, if its the current day/dat, or if its a future date.
                $month[$date->format('Y-m-d')]->day_state = $date->copy()->startOfDay()
                    ->format('Y-m-d') < Carbon::now()->startOfDay()->format('Y-m-d') ? 0 : ($date->startOfDay()->format('Y-m-d') == Carbon::now()->startOfDay()->format('Y-m-d') ? 1 : 2);
                $month[$date->format('Y-m-d')]->day_selected = 0;
                //$range stores the firs job_range (contract) that has already started, but not ended.
                $range = $job_ranges->where('start_date', '<=', $date->format('Y-m-d'))->where('end_date', '>=', $date->format('Y-m-d'))->first();
                //Check if $range->contract_type exists, if yes, range_working_hours is equal to the working_hours for that contract_type, else it's set to 0.
                $month[$date->format('Y-m-d')]->range_working_hours = isset($range->contract_type) ? $range->contractType->working_hours : 0;
                if ($users_same_day_holidays > 0) {
                    $month[$date->format('Y-m-d')]->locked_already_requested_day = count($users_department_holidays->where('date', $date->format('Y-m-d'))) >= $users_same_day_holidays;
                }

                $month[$date->format('Y-m-d')]->work_on_freedays = (bool)(isset($range->work_on_freedays) ? $range->work_on_freedays : 0);
            }

            $object_month = new \stdClass();
            $object_month->month_name = $months_names[$today->format('m') - 1] . ' ' . $today->format('Y'); //Retrieves the current month's name, by indexing the current month - 1.
            //$object_month->previous_month = $today->copy()->startOfMonth()->endOfDay() <= Carbon::now()->startOfDay();
            $object_month->previous_month = $today->copy()->endOfMonth()->startOfDay()->isBefore(Carbon::now()->endOfMonth()->startOfDay());
            $object_month->days = $month;
            $calendar[] = $object_month;
        }

        return $calendar;
    }

    /**
     * Cancel a holiday from a user.
     * 
     * Request body parameters:
     * - id: Required, the ID of the holiday to cancel.
     * - user.id: Required, the ID of the user who owns the holiday to cancel.
     * 
     * Returns a JSON response with the following structure:
     * 
     * - status: A string indicating the status of the operation.
     *   - "Success" if the holiday was cancelled successfully.
     *   - "Error" if any error occurred.
     * - text: A string with a human-readable message.
     * - calendar_data: An array with the updated calendar data.
     * - holidays_data: A JSON string with the updated holidays data.
     */
    public function cancelHolidays(Request $request)
    {
        try {
            // Perform validation
            $request->validate([
                'id' => 'required|integer|exists:holidays,id',
                'user.id' => 'required|integer|exists:users,id'
            ]);
        } catch (ValidationException $e) {
            // Return a custom error message for validation failure
            return ["status" => "Error", "text" => "Ha surgido un problema con la validación."];
        }

        // Find the holiday to cancel
        $vacancesACancelar = Holiday::find($request->id);

        // Ensure that user is treated as an array
        $userId = is_array($request->user) ? $request->user['id'] : $request->user->id;
        // Check if the holiday exists and the date is valid for cancellation
        if ($vacancesACancelar && Carbon::parse($vacancesACancelar->date)->gte(Carbon::now()->startOfDay())) {
            $vacancesACancelar->delete(); // or ->forceDelete() for soft deleted models

            return [
                "status" => "Success",
                "text" => "¡Vacaciones canceladas con éxito!",
                "calendar_data" => $this->calendar(null, $userId),
                "holidays_data" => json_decode($this->index(null, $userId))
            ];
        } else {
            return ["status" => "Error", "text" => "¡Las vacaciones no se han podido cancelar!"];
        }
    }


    /**
     * Requests holidays for the currently logged in user.
     *
     * This method receives an array of dates in the format 'Y-m-d' and requests the holidays
     * for the currently logged in user. The method will perform validation and return an
     * array with the status of the request and the updated calendar and holidays data.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function requestHolidays(Request $request)
    {
        $user = Auth::guard('web')->user();
        $user_id = $user->id;
        $job_ranges = JobRange::where('user_id', $user_id)
            ->with('contractType')
            ->get();

        $holidays_data = json_decode($this->index($request->year_passed));

        $requested_holidays_hours = 0;
        foreach ($request->requested_holidays_array as $day) {
            $hours = $job_ranges->where('start_date', '<=', $day)
                ->where('end_date', '>=', $day)
                ->first()->contractType->working_hours;
            $requested_holidays_hours += $hours;
            $holidays_data->holiday_hours_to_be_requested -= $hours;
        }

        //Checks if we are closer to a full day of vacations rather than not.
        if ($holidays_data->holiday_hours_to_be_requested > (($hours  / 2) * -1)) {
            $days_string = "";
            foreach ($request->requested_holidays_array as $day) {
                $holidays = new Holiday();
                $holidays->user_id = $user_id;
                $holidays->date = $day;
                $holidays->year_request = $request->year_passed;
                $holidays->save();
                $days_string .= "<br>" . $day;
            }
            $email_rrhh = 'bozhidaremilovdimitrov@paucasesnovescifp.cat';
            $rrhh = User::where('email', $email_rrhh)->first();
            $link = 'http://localhost:8000/departments';
            $confirmation_message = ($user_id == $rrhh->id) ? "Hemos recibido tu solicitud de vacaciones. En breve será revisada. Recibirás un correo nuevo cuando cambie de estado." : "Hemos recibido una nueva solicitud de vacaciones por parte de " . $user->name . ". Por favor, haz clic en el botón abajo para iniciar la validación.";
            try {
                Mail::to($email_rrhh)->send(new NotifyUserEmail($user, $days_string, $link, $confirmation_message));
            } catch (\Throwable $e) {
                throw new \Exception('Message: ' . $e->getMessage());
            }

            return [
                "status" => "Success",
                "text" => "Solicitud enviada correctamente, pronto recibirás una respuesta!",
                "calendar_data" => $this->calendar(),
                "holidays_data" => json_decode($this->index())
            ];
        } else {
            return ["status" => "Error", "text" => "Has solicitado más días de los que dispones."];
        }
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
        if ($passed_department_id == 'all'){
            $users_by_department = User::with('nonworkingdayzone')->get();
        } else {
            $users_by_department = User::with('nonworkingdayzone')->where('department', $passed_department_id)->get();
            $users_same_day_holidays = Department::find($passed_department_id)->users_same_day_holidays;
        }



        foreach ($users_by_department as $user) {
            $user_data = (object)[
                'id' => $user->id,
                'name' => $user->name,
                'holiday_zones' => $this->getUserHolidayZones($user->nonworkingdayzone, $all_holiday_zones)
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

    private function generateMonthData($today, $non_working_days, $user_holidays_collection)
    {

        $period = CarbonPeriod::create($today->copy()->firstOfMonth(), $today->copy()->lastOfMonth());
        $month = [];
        $adjust = true;
        $months_names = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];


        foreach ($period as $date) {
            if ($date->dayOfWeek != 1 && $adjust) {
                for ($y = 1; $y < ($date->dayOfWeek == 0 ? 7 : $date->dayOfWeek); $y++) {
                    $month[$y] = new \stdClass();
                }
            }
            $adjust = false;
            $date_string = $date->format('Y-m-d');
            $month[$date_string] = (object)[
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

            foreach ($user_holidays_collection as $user_holidays) {
                $user_holidays_day = $user_holidays->where('date', $date_string)->first();
                if ($user_holidays_day) {
                    $range = $user_holidays_day->user->jobRanges()->activeOn($user_holidays_day->date)->first();
                    $month[$date_string]->holidays[] = (object)[
                        'user_name' => $user_holidays_day->user->name,
                        'user_holiday_status' => $user_holidays_day->status_type,
                        'user_active_range' => $range,
                        'user_work_on_freedays' => $range ? $range->work_on_freedays : null
                    ];
                }
            }

            $month[$date_string]->is_holiday = !empty($month[$date_string]->holidays) ? 1 : 0;

            if (!empty($month[$date_string]->holidays)) {
                $statuses = array_column($month[$date_string]->holidays, 'user_holiday_status');
                $month[$date_string]->holiday_info = count(array_unique($statuses)) >= 2 ? 1 : 0;

                $is_wof = array_column($month[$date_string]->holidays, 'user_work_on_freedays');
                $month[$date_string]->is_work_on_freedays = count(array_unique($is_wof)) >= 2 ? 1 : 0;
            }
        }

        Carbon::setLocale('es');

        return (object)[
            'month_name' => $today->translatedFormat('F Y'),/* $months_names[$today->format('m') - 1] . ' ' . $today->format('Y'), */
            'previous_month' => $today->copy()->endOfMonth()->startOfDay()->isBefore(Carbon::now()->endOfMonth()->startOfDay()),
            'days' => $month
        ];
    }

    /**
     * This function returns a list of users that have pending holidays to be confirmed.
     * The list contains the user information and the state of their holidays.
     * If the user has pending holidays to be confirmed, the state is replaced with the pending holidays.
     * @return array
     */
    public function holidaysToConfirmList() {
        $to_be_confirmed_list = Holiday::where('status_type', 1)->with('user', 'user.nonworkingdayzone')->get();
        $list = [];
        $inserted_users = [];

        foreach ($to_be_confirmed_list as $element) {
            if (!in_array($element->user_id, $inserted_users)) {
                $object = new \stdClass();
                $object->user_info = $element->user;
                $object->holidays_state = json_decode($this->index(Carbon::now()->year, $element->user_id));
                $object->holidays_state->holidays_to_be_confirmed = [];
                if ($object->holidays_state->pending_holiday_hours_to_be_confirmed > 0 ) {
                    $object->holidays_state->holidays_to_be_confirmed = Holiday::where('user_id', $element->user_id)->where('status_type', 1)->get();
                }
                $list[] = $object;
                $inserted_users[] = $element->user_id; 
            }
        }
        return $list;
    }

    /**
     * This function updates the status of a set of holidays with the given status.
     * The function receives a Request object with the ids of the holidays to be updated and the new status.
     * The function returns an array with the status of the request and the new list of holidays to be confirmed.
     * If the request is successful, the function sends an email to the user with the updated holidays.
     * @param Request $request
     * @return array
     */
    public function holidaysUpdateState(Request $request) {
        try {
            $user_notify = null;
            $days_string = "";
            foreach ($request->ids_to_be_updated as $id) {
                $holiday_to_update = Holiday::find($id);
                $holiday_to_update->status_type = ($request->update_to ? 2 : 3);
                $holiday_to_update->validation_user = Auth::guard('web')->user()->id;
                $holiday_to_update->validation_date = Carbon::now()->format('Y-m-d H:i:s');
                $holiday_to_update->save();

                empty($user_notify) ? $user_notify = $holiday_to_update->user_id : false;
                $days_string .= $holiday_to_update->date . '<br>';
            }

            try {
                $user = User::find($user_notify);
                $link = 'http://localhost:8000/vacations';
                $confirmation_message = "Tu solicitud de vacaciones ha sido actualizada a " . ($request->update_to ? "'Confirmada'. " : "'Denegada'. ") . "Haz clic en el siguiente enlace para revisar tus vacaciones.";
                try {
                    Mail::to($user->email)->send(new NotifyUserEmail($user, $days_string, $link, $confirmation_message));
                } catch (\Throwable $e) {
                    throw new \Exception('Message: ' . $e->getMessage());
                }
    
                return [
                    "status" => "Success",
                    "text" => "Solicitud actualizada a " . ($request->update_to ? "Confirmada " : "Denegada ") . "correctamente.",
                    "list" => $this->holidaysToConfirmList(),
                ];
            } catch (Exception $ex) {

            }
        } catch (Exception $ex) {
            return ["status" => "Error", "text" => "Ha surgido un error durante la validación."];
        }
    }

}
