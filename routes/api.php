<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
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

    // Company Workspace
    Route::get('companies', [CompanyController::class, 'index']);

    //Company Workspace Invitation
    Route::post('companies/{company}/invite', [CompanyInvitationController::class, 'invite'])->middleware('company.permission:invite_company_member');
    Route::post('companies/{company}/invitations/{invitation}/reinvite', [CompanyInvitationController::class, 'reinvite'])->middleware('company.permission:invite_company_member');

    // Projects Invitation
    Route::post('projects/{id}/member/invite', [ProjectInvitationController::class, 'invite'])->middleware('project.permission:invite_project_member');
    Route::post('companies/{company}/projects', [ProjectController::class, 'store'])->middleware('company.permission:create_project');
    Route::post('projects/{id}/invitations/{invitation_id}/reinvite', [ProjectInvitationController::class, 'reinvite'])->middleware('project.permission:invite_project_member');

    // Tasks
    Route::patch('tasks/{task}/complete', [TaskController::class, 'markAsComplete'])->middleware('project.permission:update_task');
    Route::patch('tasks/{task}/update', [TaskController::class, 'updateStatus'])->middleware('project.permission:update_task');
    Route::post('tasks', [TaskController::class, 'store'])->middleware('project.permission:create_task');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])->middleware('project.permission:delete_task');

    // Route::apiResource('tasks', TaskController::class);

    // Route::get('users/{id}/tasks', [TaskController::class, 'getUserTasks']);

    Route::get('projects/{id}/tasks', [ProjectController::class, 'getProjectTasks']);
    Route::post('projects/{id}/members', [ProjectController::class, 'addMember']);
    Route::apiResource('projects', ProjectController::class);

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
Route::get('invitations/accept/{token}', [ProjectInvitationController::class, 'accept'])->middleware('signed')->name('invitation.accept');
Route::get('invitations/decline/{token}', [ProjectInvitationController::class, 'decline']);

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
