<?php
namespace App\Services;

use App\Jobs\SendProjectInvitationEmail;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProjectInvitationService
{
    public function sendInvitation($projectId, $email, $roleId, $invitedBy)
    {
        $project = Project::findOrFail($projectId);

        $user = User::where('email', $email)->first();

        if ($user && $user->company_id !== $project->company_id) {
            throw new \Exception('User belongs to another company.');
        }

        $existingMember = ProjectMember::where('project_id', $projectId)->whereHas('user', fn($q) => $q->where('email', $email))->exists();

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
            'invited_by' => $invitedBy,
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $token]
        );

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

    public function resendInvitation($invitation)
    {

        if (in_array($invitation->status, ['accepted', 'cancelled'])) {
            throw new \Exception("Cannot resend. Current status is: {$invitation->status}");
        }

        $invitation->update([
            'expires_at' => now()->addDays(3),
            'status'     => 'pending',
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $invitation->token]
        );

        SendProjectInvitationEmail::dispatch($invitation, $acceptUrl);
    }

}
