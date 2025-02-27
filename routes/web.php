<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WeeklyScheduleController;
use App\Http\Controllers\JobRangeController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        // User is authenticated, show the Welcome page
        return Inertia::render('Dashboard', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    } else {
        // User is not authenticated, redirect to login
        return redirect()->route('login');
    }
});


Route::middleware(['auth', 'verified'])->get('/api/user', function (Request $request) {
    return $request->user();
});

/* PROTECTED AUTH + VERIFIED */
Route::middleware(['auth', 'verified'])->group(function () {
    /* ---USER ROUTES--- */
    /* API */ Route::get('/api/user', function (Request $request) {
        return $request->user();
    });

    /* API */ Route::get('/api/user/{id}', [UserController::class, 'getUserById'])
        ->name('getUserById');

    /* API */ Route::get('api/user/vacations/{year}/{id}', [HolidayController::class, 'index'])
        ->name('userVacationsByYearAndId');

    /* API */ Route::get('api/user/vacations/{year}', [HolidayController::class, 'index'])
        ->name('userVacationsByYear');

    

    /* ---HOME/DASHBOARD ROUTES--- */
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })
        ->name('dashboard');


    /* ---DEPARTMENTS ROUTES--- */
    // This route renders the 'Departments' view
    Route::get('/departments', [DepartmentController::class, 'index'])
        ->name('departments');
    
    /* API */ Route::get('/api/department_name/{id}', [DepartmentController::class, 'getDepartmentNameById'])
        ->name('getDepartmentNameById');
    
    /* API */ Route::get('api/calendar_department/{year}', [HolidayController::class, 'calendarDepartment'])
        ->name('calendarDepartmentByYear');

    /* API */ Route::get('api/calendar_department/{year}/{id}', [HolidayController::class, 'calendarDepartment'])
            ->name('calendarDepartmentByYearAndId');

    /* API */ Route::get('api/calendar_department/{year}/{id}/{year_month}', [HolidayController::class, 'calendarDepartment'])
        ->name('calendarDepartmentByYearByIdAndMonth');

    // This route fetches users by department ID
    Route::get('/departments/{departament_id}/users', [UserController::class, 'getUsersByDepartament'])
        ->name('departments.users');


    /* ---VACATIONS ROUTES--- */
    // This route renders the 'Vacations' view
    Route::get('/vacations', function () {
        return Inertia::render('Vacations');
    })
        ->name('vacations');

    /* API */ Route::get('api/calendar/{year}/{id}', [HolidayController::class, 'calendar'])
        ->name('calendarByYearAndId');
    
    /* API */ Route::get('api/calendar/{year}', [HolidayController::class, 'calendar'])
        ->name('calendarByYear');

    /* API */ Route::get('api/vacations_to_be_confirmed', [HolidayController::class, 'holidaysToConfirmList'])
        ->name('holidaysToConfirmList');

    Route::post('vacations/delete', [HolidayController::class, 'cancelHolidays'])
        ->name('cancelVacations');

    Route::post('vacations/request', [HolidayController::class, 'requestHolidays'])
        ->name('requestVacations');

    /* ---SCHEDULE ROUTES--- */
    // This route renders the 'Schedule' view
    Route::get('/schedule', [ScheduleController::class, 'getDepartments'])
        ->name('schedule');
    
    Route::post('/schedule/saveweeklyschedule', [WeeklyScheduleController::class, 'saveWeeklySchedule'])
        ->name('saveSchedule');

    Route::post('/schedule/weekly/{week_number}/{department_id}/{year}', [ScheduleController::class, 'weeklySchedule'])
        ->name('WeeklySchedule');

    Route::delete('/schedules_template_delete/{department_id}/{users_available}', [ScheduleController::class, 'removeSchedules'])
        ->name('removeScheduleTemplate');

    Route::post('/schedule/savescheduletemplate', [ScheduleController::class, 'addScheduleTemplate'])
        ->name('addScheduleTemplate');

    /* API */ Route::get('api/week_dates/{year}/{week_number}', [ScheduleController::class, 'getDatesForWeek'])
        ->name('getDatesForWeek');

    /* API */ Route::get('api/week_numbers_by_month/{year}/{month}', [ScheduleController::class, 'getWeekNumbersForMonth'])
        ->name('getWeekNumbersForMonth');

    /* API */ Route::get('api/weekly_schedules_for_month/{department_id}/{year}/{month}', [ScheduleController::class, 'getWeeklySchedulesForMonth'])
        ->name('getWeeklySchedulesForMonth');

    /* API */ Route::get('api/schedules_template_check/{department_id}/{users_available}', [ScheduleController::class, 'checkForScheduleTemplate'])
        ->name('checkForScheduleTemplate');


    /* ---VACATION MANAGER ROUTES--- */
    // This route renders the 'Vacations' view
    Route::get('/manager', [ManagerController::class, 'index'])
        ->name('manager');

    Route::get('/manager/get_ranges/{user_id}', [ManagerController::class, 'getRanges'])
        ->name('getRange');

    Route::post('/manager/add/range', [ManagerController::class, 'addJobRange'])
        ->name('addRange');
    
    Route::post('/manager/update/holidays', [HolidayController::class, 'holidaysUpdateState'])
        ->name('updateHolidays');


    /* ---ADMINISTRATION ROUTES--- */
    // This route renders the 'VacationsManager' view
    Route::get('/fetch/users', [UserController::class, 'getAllUsers'])
        ->name('fetchUsers');

    Route::get('/fetch/departments', [ManagerController::class, 'getDepartments'])
        ->name('fetchDepartments');

    Route::get('/fetch/zones', [ManagerController::class, 'getZones'])
        ->name('fetchZones');

    Route::put('/update/user/{user_id}', [UserController::class, 'updateUser'])
        ->name('updateUser');

    /* API */ Route::get('api/allJobRanges', [JobRangeController::class, 'getAll'])
        ->name('getAllJobRanges');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/roles/permissions', [UserController::class, 'index'])->name('roles.permissions');

require __DIR__ . '/auth.php';
