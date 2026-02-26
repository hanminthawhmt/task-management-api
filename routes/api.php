<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::patch('tasks/{task}/complete', [TaskController::class, 'markAsComplete']);
Route::patch('tasks/{id}/update', [TaskController::class, 'updateStatus']);
Route::resource('tasks', TaskController::class);
