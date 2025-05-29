<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\AuthClientController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Login Routes for Admin and Employees
Route::post('admin/login', [AuthAdminController::class, 'login']);
Route::post('admin/refresh', [AuthAdminController::class, 'refresh']);
Route::get('admin/details', [AuthAdminController::class, 'detailsAdmin']);

// Login Routes for Clients
Route::prefix('client')->group(function () {
    Route::post('login', [AuthClientController::class, 'login']);
    Route::post('refresh', [AuthClientController::class, 'refresh']);
    Route::get('details', [AuthClientController::class, 'detailsClient']);
});


// Manage Admin Routes
Route::middleware(['admin.jwt'])->group(function () {
    Route::middleware(['admin.access'])->group(function () {
        Route::get('admin', [AdminController::class, 'index']);
        Route::get('/admin/staffs/{projectId}', [AdminController::class, 'staffOnly']);
        Route::post('admin/create', [AdminController::class, 'store']);
        Route::put('admin/update/{id}', [AdminController::class, 'update']);
        Route::delete('admin/delete/{id}', [AdminController::class, 'destroy']);
    });
});

// Clients Routes
Route::middleware(['admin.jwt'])->group(function () {
    
    Route::middleware(['admin.access'])->group(function () {
        Route::post('admin/client/create', [ClientController::class, 'store']);
        Route::put('admin/client/update/{id}', [ClientController::class, 'update']);
        Route::delete('admin/client/delete/{id}', [ClientController::class, 'destroy']);
        Route::get('admin/clients', [ClientController::class, 'index']);
        Route::get('admin/client/{id}', [ClientController::class, 'show']);
    });
});

Route::get('admin/project/bill/{id}', [ProjectController::class, 'generateBill']);
Route::get('client/project/bill/{id}', [ProjectController::class, 'generateBill']);

// Projects Routes
Route::middleware(['admin.jwt'])->group(function () {

    Route::middleware(['admin.access'])->group(function () {
        Route::get('admin/projects', [ProjectController::class, 'index']);
        Route::get('admin/project-info', [ProjectController::class, 'getProjectWithInfo']);

        Route::post('admin/project/create', [ProjectController::class, 'store']);
        Route::put('admin/project/update/{id}', [ProjectController::class, 'update']);
        Route::delete('admin/project/delete/{id}', [ProjectController::class, 'destroy']);
        Route::post('admin/project/affect-employee/{id}', [ProjectController::class, 'addUserToProject']);
        Route::post('admin/project/remove-employee', [ProjectController::class, 'removeUserFromProject']);
    });
});


// Tasks Routes
Route::middleware('admin.jwt')->get('admin/tasks/my-tasks', [TaskController::class, 'myTasks']);
Route::middleware(['admin.jwt'])->group(function () {
    Route::get('admin/task/{id}', [TaskController::class, 'show']);
    Route::get('admin/tasks', [TaskController::class, 'index']);
    Route::middleware(['admin.access'])->group(function () {
        Route::post('admin/task/create', [TaskController::class, 'store']);
        Route::put('admin/task/update/{id}', [TaskController::class, 'update']);
        Route::delete('admin/task/delete/{id}', [TaskController::class, 'destroy']);
        
        // Task assignment admin-only routes
        Route::post('admin/task/assign', [TaskAssignmentController::class, 'store']);
        Route::delete('admin/task/assignment/{assignmentId}', [TaskAssignmentController::class, 'destroy']);

        // Calendar Event routes
        Route::get('admin/calendar-events', [CalendarEventController::class, 'index']);

    });
    
    // Assignment routes accessible to assigned users
    Route::get('admin/task/{taskId}/assignments', [TaskAssignmentController::class, 'taskAssignments']);
    Route::put('admin/task/assignment/{assignmentId}/status', [TaskAssignmentController::class, 'updateAssignmentStatus']);
    Route::put('admin/task/update-status/{taskId}', [TaskController::class, 'updateStatus']);

});



// Count Routes
Route::middleware(['admin.jwt'])->group(function () {
    Route::get('admin/count-projects', [ProjectController::class, 'countProjects']);
    Route::get('admin/count-clients', [ClientController::class, 'countClients']);
    Route::get('admin/count-tasks', [TaskController::class, 'countTasks']);
    Route::get('admin/count-employees', [AdminController::class, 'countStaff']);
    Route::get('admin/project-by-month', [ProjectController::class, 'countProjectsByMonth']);
    Route::get('admin/task-by-month', [TaskController::class, 'countTasksByMonth']);
});


// Client Routes
Route::get('/client/projects', [ProjectController::class, 'getProjectsForEachClient']);
Route::get('/client/calendar-events', [CalendarEventController::class, 'clientEvents']);
Route::get('/client/project-by-month', [ProjectController::class, 'countClientProjectsByMonth']);

