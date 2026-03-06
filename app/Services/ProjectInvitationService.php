<?php
namespace App\Services;

use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProjectInvitationService
{
    public function sendInvitation($projectId, $email, $roleId, $invitedBy)
    {
        $token = Str::uuid();

        $invitation = ProjectInvitation::create([
            'project_id' => $projectId,
            'role_id'    => $roleId,
            'email'      => $email,
            'invited_by' => $invitedBy,
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new ProjectInvitationMail($invitation));

        return $invitation;

    }

    public function acceptInvitation($token, $userId)
    {
        $invitation = ProjectInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            throw new \Exception("Invitation already used");
        }

        ProjectMember::create([
            'project_id' => $invitation->project_id,
            'user_id'    => $userId,
            'role_id'    => $invitation->role_id,
        ]);

        $invitation->update(['status' => 'accepted']);

        return $invitation;
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
}
