<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DepartmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/user/{id}', [UserController::class, 'getUserById']);

Route::middleware('auth:sanctum')->get('/user/vacations/{year}/{id}', [HolidayController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user/vacations/{year}', [HolidayController::class, 'index']);
Route::middleware('auth:sanctum')->get('/calendar/{year}/{id}', [HolidayController::class, 'calendar']);
Route::middleware('auth:sanctum')->get('/calendar/{year}', [HolidayController::class, 'calendar']);
Route::middleware('auth:sanctum')->get('/vacations_to_be_confirmed', [HolidayController::class, 'holidaysToConfirmList']); /* NEW */
Route::middleware('auth:sanctum')->get('/calendar_department/{year}', [HolidayController::class, 'calendarDepartment']);
Route::middleware('auth:sanctum')->get('/calendar_department/{year}/{id}', [HolidayController::class, 'calendarDepartment']);
Route::middleware('auth:sanctum')->get('/calendar_department/{year}/{id}/{year_month}', [HolidayController::class, 'calendarDepartment']);
Route::middleware('auth:sanctum')->get('/department_name/{id}', [DepartmentController::class, 'getDepartmentNameById']);
/* Route::middleware('auth:sanctum')->get('/schedule/weekly/{week_number}/{department_id}', [ScheduleController::class, 'weeklySchedule']); */
Route::middleware('auth:sanctum')->get('/week_dates/{year}/{week_number}', [ScheduleController::class, 'getDatesForWeek']);
Route::middleware('auth:sanctum')->get('/week_numbers_by_month/{year}/{month}', [ScheduleController::class, 'getWeekNumbersForMonth']);
Route::middleware('auth:sanctum')->get('/weekly_schedules_for_month/{department_id}/{year}/{month}', [ScheduleController::class, 'getWeeklySchedulesForMonth']);
Route::middleware('auth:sanctum')->get('/schedules_template_check/{department_id}/{users_available}', [ScheduleController::class, 'checkForScheduleTemplate']);

