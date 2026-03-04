<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::patch('tasks/{task}/complete', [TaskController::class, 'markAsComplete']);
    Route::patch('tasks/{id}/update', [TaskController::class, 'updateStatus']);

    Route::apiResource('tasks', TaskController::class);

    Route::get('users/{id}/tasks', [TaskController::class, 'getUserTasks']);
    Route::get('projects/{id}/tasks', [ProjectController::class, 'getProjectTasks']);
    Route::apiResource('projects', ProjectController::class);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::apiResource('roles', RoleController::class);
