<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('tasks/{task}/complete', [TaskController::class, 'markAsComplete']);
    Route::patch('tasks/{id}/update', [TaskController::class, 'updateStatus']);
    Route::resource('tasks', TaskController::class);
});
