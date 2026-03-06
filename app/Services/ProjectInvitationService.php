<?php
namespace App\Services;

use App\Jobs\SendProjectInvitationEmail;
use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProjectInvitationService
{
    public function sendInvitation($projectId, $email, $roleId, $invitedBy)
    {
        // $existingMember = ProjectMember::where('project_id', $project->id)
        //     ->whereHas('user', fn($q) => $q->where('email', $email))
        //     ->exists();

        // if ($existingMember) {
        //     throw new \Exception('User already belongs to this project.');
        // }

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

        //Mail::to($email)->send(new ProjectInvitationMail($invitation, $acceptUrl));
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

        DB::transaction(function () use ($invitation, $user) {

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

    public function resendInvitation($invitation)
    {
        $resendableStatuses = ['pending', 'expired', 'cancelled'];

        if ($invitation->status === 'pending') {
            throw new \Exception('Cannot resend invitation.');
        }

        $invitation->update([
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $invitation->token]
        );

        Mail::to($invitation->email)
            ->send(new ProjectInvitationMail($invitation, $acceptUrl));
    }

}
