<?php
namespace App\Services\Invitation;

use App\Jobs\SendProjectInvitationEmail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProjectInvitationService
{
    public function __construct(protected ActivityLogService $logService)
    {}
    public function sendInvitation($projectId, $email, $roleId, $User)
    {
        $project = Project::findOrFail($projectId);

        $user = User::where('email', $email)->first();

        if (! $user) {
            throw new \Exception('User haven\'t registered in this organization');
        }

        $existingMember = ProjectMember::where('project_id', $projectId)
            ->whereHas('user', fn($q) => $q->where('email', $email))->exists();

        if ($existingMember) {
            throw new \Exception('User already belongs to this project.');
        }

        $existingInvite = ProjectInvitation::where('project_id', $projectId)
            ->where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            throw new \Exception('Pending invitation already exists.');
        }

        $token = Str::uuid();

        $invitation = ProjectInvitation::create([
            'project_id' => $projectId,
            'role_id'    => $roleId,
            'email'      => $email,
            'invited_by' => $User->id,
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $token]
        );

        \Log::info('Invitation signed URL: ' . $acceptUrl);

        // Activity Log Here
        $this->logService->log($User, 'invited_project_member', $invitation, ['email' => $email]);

        SendProjectInvitationEmail::dispatch($invitation, $acceptUrl);

        return $invitation;

    }

    public function acceptInvitation($token, $user)
    {
        $invitation = ProjectInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            throw new \Exception("Invitation already used");
        }

        if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
            $invitation->update(['status' => 'expired']);
            abort(400, 'Invitation expired.');
        }

        if ($invitation->email !== $user->email) {
            abort(403, 'This invitation was sent to a different email.');
        }

        DB::transaction(function () use ($invitation, $user) {

            $existingMember = ProjectMember::where('project_id', $invitation->project_id)->where('user_id', $user->id)->exists();

            if ($existingMember) {
                throw new \Exception('User already belongs to this project.');
            }

            ProjectMember::create([
                'project_id' => $invitation->project_id,
                'user_id'    => $user->id,
                'role_id'    => $invitation->role_id,
            ]);

            $invitation->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
            ]);
        });

        $this->logService->log($user, 'accept_project_invitation', $invitation, ['email' => $invitation->email]);

        return $invitation->fresh();
    }

    public function declineInvitation($token)
    {
        $invitation = ProjectInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            throw new \Exception("Invitation already used");
        }

        $invitation->update(['status' => 'declined']);

        return $invitation;
    }

    public function resendInvitation($invitation, $user)
    {

        if ($invitation->status === 'accepted') {
            throw new \Exception("Invitation already accepted.");
        }

        if ($invitation->status === 'cancelled') {
            throw new \Exception("Invitation was cancelled.");
        }

        $invitation->update([
            'token'      => Str::uuid(),
            'expires_at' => now()->addDays(3),
            'status'     => 'pending',
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $invitation->token]
        );

        $this->logService->log($user, 'resent_project_invitation', $invitation, ['email' => $invitation->email]);

        SendProjectInvitationEmail::dispatch($invitation, $acceptUrl);
    }

}
