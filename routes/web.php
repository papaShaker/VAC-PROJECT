<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WeeklyScheduleController;
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

/* Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); */

/* PROTECTED AUTH + VERIFIED */
Route::middleware(['auth', 'verified'])->group(function () {

    /* ---HOME/DASHBOARD ROUTES--- */
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })
        ->name('dashboard');

    /* ---DEPARTMENTS ROUTES--- */
    // This route renders the 'Departments' view
    Route::get('/departments', [DepartmentController::class, 'index'])
        ->name('departments');

    // This route fetches users by department ID
    Route::get('/departments/{departament_id}/users', [UserController::class, 'getUsersByDepartament'])
        ->name('departments.users');

    /* ---VACATIONS ROUTES--- */
    // This route renders the 'Vacations' view
    Route::get('/vacations', function () {
        return Inertia::render('Vacations');
    })
        ->name('vacations');

    
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
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__ . '/auth.php';
