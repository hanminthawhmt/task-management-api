<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyInvitationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectInvitationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->group(function () {

    // logout
    Route::post('logout', [AuthController::class, 'logout']);
    // token refresh
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Route::patch('tasks/{task}/complete', [TaskController::class, 'markAsComplete']);
    // Route::patch('tasks/{id}/update', [TaskController::class, 'updateStatus']);

    // Route::apiResource('tasks', TaskController::class);

    // Route::get('users/{id}/tasks', [TaskController::class, 'getUserTasks']);

    // Only company owner can invite members to the project
    Route::post('projects/{id}/member/invite', [ProjectInvitationController::class, 'invite'])->middleware('project.permission:invite_project_member');
    // Only company owner can invite members to the companywork space
    Route::post('companies/{id}/member/invite', [CompanyInvitationController::class, 'invite'])->middleware('company.permission:invite_company_member');

    Route::post('companies/{id}/projects', [ProjectController::class, 'store'])->middleware('company.permission:create_project');

    Route::get('projects/{id}/tasks', [ProjectController::class, 'getProjectTasks']);
    Route::post('projects/{id}/members', [ProjectController::class, 'addMember']);
    Route::apiResource('projects', ProjectController::class);

    // company owner, company admin, company manager, project owner, project manager, project developer can create task
    Route::post('tasks', [TaskController::class, 'store'])->middleware('project.permission:create_task');
});

// can register as a company owner or can register as a company member using invitation token
Route::post('register', [AuthController::class, 'register']);
// can register as a platform admin
Route::post('admin/register', [AuthController::class, 'registerAsAdmin']);
// login for everyone in the system
Route::post('login', [AuthController::class, 'login']);

Route::apiResource('roles', RoleController::class);

Route::apiResource('invitations', CompanyInvitationController::class);

// Route::apiResource('invitations', ProjectInvitationController::class);
Route::post('projects/invitations/send', [ProjectInvitationController::class, 'createProjectAndInvite']);
Route::get('invitations/accept/{token}', [ProjectInvitationController::class, 'accept'])->middleware('signed')->name('invitation.accept');
Route::get('invitations/decline/{token}', [ProjectInvitationController::class, 'decline']);
Route::post('invitations/{id}/resend', [ProjectInvitationController::class, 'reinvite']);

Route::get('/test-email', function () {
    try {
        Mail::raw('Mailtrap test from ' . config('app.url'), function ($message) {
            $message->to('hanminthaw@test.com')
                ->subject('XAMPP Connectivity Test');
        });
        return response()->json(['message' => 'Email sent! Check Mailtrap.']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
