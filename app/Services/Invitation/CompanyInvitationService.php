<?php
namespace App\Services\Invitation;

use App\Jobs\SendCompanyInvitationEmail;
use App\Models\CompanyInvitation;
use App\Models\CompanyMember;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CompanyInvitationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected ActivityLogService $logService)
    {}

    public function sendInvitation($company, $roleId, $email, $User)
    {
        $user = User::where('email', $email)->first();

        $existingEmployee = CompanyMember::where('company_id', $company->id)
            ->whereHas('user', fn($q) => $q->where('email', $email))->exists();

        if ($existingEmployee) {
            throw new \Exception('User already in this organization.');
        }

        $existingInvite = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            throw new \Exception('Pending invitation already exists.');
        }

        $token = Str::uuid();

        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email'      => $email,
            'role_id'    => $roleId,
            'token'      => $token,
            'status'     => 'pending',
            'invited_by' => $User->id,
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $token]
        );

        $this->logService->log($User, 'invited_company_member', $invitation, ['email' => $email]);
        SendCompanyInvitationEmail::dispatch($invitation, $acceptUrl);

        return $invitation;

    }

    public function declineInvitation($token)
    {
        $invitation = CompanyInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== "pending") {
            throw new \Exception("Invitation already used");
        }

        $invitation->update(['status' => 'declined']);
        
        return $invitation;
    }

    public function resendInvitation($invitation, $user)
    {
        if ($invitation->status === 'accepted') {
            throw new \Exception("Invitation already accepted");
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

        $this->logService->log($user, 'resent_company_invitation', $invitation, ['email' => $invitation->email]);

        SendCompanyInvitationEmail::dispatch($invitation, $acceptUrl);
    }
}
