<?php
namespace App\Services\Invitation;

use App\Jobs\SendCompanyInvitationEmail;
use App\Models\CompanyInvitation;
use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CompanyInvitationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function sendInvitation($company, $roleId, $email, $userId)
    {
        $company_id = $company->id;

        $user = User::where('email', $email)->first();

        $existingEmployee = CompanyMember::where('company_id', $company_id)
            ->whereHas('user', fn($q) => $q->where('email', $email))->exists();

        if ($existingEmployee) {
            throw new \Exception('User already in this organization.');
        }

        $existingInvite = CompanyInvitation::where('company_id', $company_id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            throw new \Exception('Pending invitation already exists.');
        }

        $token = Str::uuid();

        $invitation = CompanyInvitation::create([
            'company_id' => $company_id,
            'email'      => $email,
            'role_id'    => $roleId,
            'token'      => $token,
            'status'     => 'pending',
            'invited_by' => $userId,
            'expires_at' => now()->addDays(3),
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addDays(3),
            ['token' => $token]
        );

        SendCompanyInvitationEmail::dispatch($invitation, $acceptUrl);

        return $invitation;

    }

    public function acceptInvitation()
    {

    }

    public function declineInvitation()
    {

    }

    public function resendInvitation()
    {

    }
}
