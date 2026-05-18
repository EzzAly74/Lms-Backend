<?php

use App\Http\Controllers\apis\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Attendance Routes — /api/v1/attendance
|--------------------------------------------------------------------------
| Admin: GET  /api/v1/attendance  — paginated list with filters
|        POST /api/v1/attendance  — record (status=1) or remove (status=0)
*/

Route::middleware(['auth.user', 'role:Admin'])->group(function () {
    Route::get('attendance',  [AttendanceController::class, 'index']);
    Route::post('attendance', [AttendanceController::class, 'store']);
});

Route::middleware(['auth.user', 'role:User'])->group(function () {
    Route::get('courses/{course}/my-attendance', [AttendanceController::class, 'myCourseAttendance']);
});
