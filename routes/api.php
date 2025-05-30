<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeePositionHistoryController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api;" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/', [SiteController::class, 'index']);

    Route::post('/auth/login', [AuthController::class, 'login']); //->middleware(['signature']);
    Route::post('/auth/logout', [AuthController::class, 'logout']); //->middleware(['signature']);
    Route::get('/auth/profile', [AuthController::class, 'profile'])->middleware(['auth.api']);

    Route::get('/users', [UserController::class, 'index'])->middleware(['auth.api', 'role:user.view']);
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware(['auth.api', 'role:user.view']);
    Route::post('/users', [UserController::class, 'store'])->middleware(['auth.api', 'role:user.create']);
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware(['auth.api', 'role:user.update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware(['auth.api', 'role:user.delete']);

    Route::get('/roles', [RoleController::class, 'index'])->middleware(['auth.api', 'role:roles.view']);
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware(['auth.api', 'role:roles.view']);
    Route::post('/roles', [RoleController::class, 'store'])->middleware(['auth.api', 'role:roles.create']);
    Route::put('/roles', [RoleController::class, 'update'])->middleware(['auth.api', 'role:roles.update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware(['auth.api', 'role:roles.delete']);

    Route::get('/employees', [EmployeeController::class, 'index'])->middleware(['auth.api', 'role:user.view']);
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware(['auth.api', 'role:user.create']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->middleware(['auth.api', 'role:user.view']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->middleware(['auth.api', 'role:user.update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->middleware(['auth.api', 'role:user.delete']);
    Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore'])->middleware(['auth.api', 'role:user.update']);

    Route::get('/positions', [PositionController::class, 'index'])->middleware(['auth.api', 'role:positions.view']);
    Route::post('/positions', [PositionController::class, 'store'])->middleware(['auth.api', 'role:positions.create']);
    Route::get('/positions/{id}', [PositionController::class, 'show'])->middleware(['auth.api', 'role:positions.view']);
    Route::put('/positions/{id}', [PositionController::class, 'update'])->middleware(['auth.api', 'role:positions.update']);
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->middleware(['auth.api', 'role:positions.delete']);
    Route::post('/positions/{id}/restore', [PositionController::class, 'restore'])->middleware(['auth.api', 'role:positions.update']);

    Route::get('/departments', [DepartmentController::class, 'index'])->middleware(['auth.api', 'role:departments.view']);
    Route::post('/departments', [DepartmentController::class, 'store'])->middleware(['auth.api', 'role:departments.create']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->middleware(['auth.api', 'role:departments.view']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->middleware(['auth.api', 'role:departments.update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->middleware(['auth.api', 'role:departments.delete']);
    Route::post('/departments/{id}/restore', [DepartmentController::class, 'restore'])->middleware(['auth.api', 'role:departments.update']);

    Route::get('/payrolls', [PayrollController::class, 'index'])->middleware(['auth.api', 'role:payrolls.view']);
    Route::post('/payrolls', [PayrollController::class, 'store'])->middleware(['auth.api', 'role:payrolls.create']);
    Route::get('/payrolls/{id}', [PayrollController::class, 'show'])->middleware(['auth.api', 'role:payrolls.view']);
    Route::put('/payrolls/{id}', [PayrollController::class, 'update'])->middleware(['auth.api', 'role:payrolls.update']);
    Route::delete('/payrolls/{id}', [PayrollController::class, 'destroy'])->middleware(['auth.api', 'role:payrolls.delete']);
    Route::post('/payrolls/{id}/restore', [PayrollController::class, 'restore'])->middleware(['auth.api', 'role:payrolls.update']);

    Route::get('/attendances', [AttendanceController::class, 'index'])->middleware(['auth.api', 'role:attendance.view']);
    Route::post('/attendances', [AttendanceController::class, 'store'])->middleware(['auth.api', 'role:attendance.create']);
    Route::get('/attendances/{id}', [AttendanceController::class, 'show'])->middleware(['auth.api', 'role:attendance.view']);
    Route::put('/attendances/{id}', [AttendanceController::class, 'update'])->middleware(['auth.api', 'role:attendance.update']);
    Route::delete('/attendances/{id}', [AttendanceController::class, 'destroy'])->middleware(['auth.api', 'role:attendance.delete']);
    Route::post('/attendances/{id}/restore', [AttendanceController::class, 'restore'])->middleware(['auth.api', 'role:attendance.update']);

    Route::get('/shifts', [ShiftController::class, 'index'])->middleware(['auth.api', 'role:shifts.view']);
    Route::post('/shifts', [ShiftController::class, 'store'])->middleware(['auth.api', 'role:shifts.create']);
    Route::get('/shifts/{id}', [ShiftController::class, 'show'])->middleware(['auth.api', 'role:shifts.view']);
    Route::put('/shifts/{id}', [ShiftController::class, 'update'])->middleware(['auth.api', 'role:shifts.update']);
    Route::delete('/shifts/{id}', [ShiftController::class, 'destroy'])->middleware(['auth.api', 'role:shifts.delete']);
    Route::post('/shifts/{id}/restore', [ShiftController::class, 'restore'])->middleware(['auth.api', 'role:shifts.update']);

    Route::get('/leave-types', [LeaveTypeController::class, 'index'])->middleware(['auth.api', 'role:leave-types.view']);
    Route::post('/leave-types', [LeaveTypeController::class, 'store'])->middleware(['auth.api', 'role:leave-types.create']);
    Route::get('/leave-types/{id}', [LeaveTypeController::class, 'show'])->middleware(['auth.api', 'role:leave-types.view']);
    Route::put('/leave-types/{id}', [LeaveTypeController::class, 'update'])->middleware(['auth.api', 'role:leave-types.update']);
    Route::delete('/leave-types/{id}', [LeaveTypeController::class, 'destroy'])->middleware(['auth.api', 'role:leave-types.delete']);

    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->middleware(['auth.api', 'role:leave-requests.view']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->middleware(['auth.api', 'role:leave-requests.create']);
    Route::get('/leave-requests/{id}', [LeaveRequestController::class, 'show'])->middleware(['auth.api', 'role:leave-requests.view']);
    Route::put('/leave-requests/{id}', [LeaveRequestController::class, 'update'])->middleware(['auth.api', 'role:leave-requests.update']);
    Route::delete('/leave-requests/{id}', [LeaveRequestController::class, 'destroy'])->middleware(['auth.api', 'role:leave-requests.delete']);
    Route::post('/leave-requests/{id}/restore', [LeaveRequestController::class, 'restore'])->middleware(['auth.api', 'role:leave-requests.update']);
    Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve'])->middleware(['auth.api', 'role:leave-requests.approve']);
    Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject'])->middleware(['auth.api', 'role:leave-requests.reject']);

    Route::prefix('position-histories')->group(function () {
        Route::get('/', [EmployeePositionHistoryController::class, 'index']);
        Route::post('/', [EmployeePositionHistoryController::class, 'store']);
        Route::get('/{id}', [EmployeePositionHistoryController::class, 'show']);
        Route::put('/{id}', [EmployeePositionHistoryController::class, 'update']);
        Route::delete('/{id}', [EmployeePositionHistoryController::class, 'destroy']);

        Route::get('/department-stats', [EmployeePositionHistoryController::class, 'departmentStats']);
        Route::get('/position-stats', [EmployeePositionHistoryController::class, 'positionStats']);
        Route::get('/organization-structure', [EmployeePositionHistoryController::class, 'organizationStructure']);
        Route::get('/salary-comparison/department', [EmployeePositionHistoryController::class, 'salaryComparisonByDepartment']);
        Route::get('/salary-comparison/position', [EmployeePositionHistoryController::class, 'salaryComparisonByPosition']);
    });

    Route::prefix('employees/{employeeId}')->group(function () {
        Route::get('/positions', [EmployeePositionHistoryController::class, 'employeeHistory']);
        Route::post('/positions', [EmployeePositionHistoryController::class, 'assignPosition']);
        Route::get('/position-changes', [EmployeePositionHistoryController::class, 'employeePositionChanges']);
        Route::post('/salary', [EmployeePositionHistoryController::class, 'updateSalary']);
        Route::get('/salary-history', [EmployeePositionHistoryController::class, 'salaryHistory']);
        Route::get('/salary-increase', [EmployeePositionHistoryController::class, 'salaryIncreasePercentage']);
    });

    Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware(['auth.api', 'role:announcements.view']);
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware(['auth.api', 'role:announcements.create']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])->middleware(['auth.api', 'role:announcements.view']);
    Route::put('/announcements/{id}', [AnnouncementController::class, 'update'])->middleware(['auth.api', 'role:announcements.update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])->middleware(['auth.api', 'role:announcements.delete']);
    Route::post('/announcements/{id}/restore', [AnnouncementController::class, 'restore'])->middleware(['auth.api', 'role:announcements.update']);

});

Route::get('/', function () {
    return response()->failed(['Endpoint yang anda minta tidak tersedia']);
});

/**
 * Jika Frontend meminta request endpoint API yang tidak terdaftar
 * maka akan menampilkan HTTP 404
 */
Route::fallback(function () {
    return response()->failed(['Endpoint yang anda minta tidak tersedia']);
});
