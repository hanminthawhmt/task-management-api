<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendInvitationRequest;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Services\Invitation\CompanyInvitationService;

class CompanyInvitationController extends Controller
{
    public function __construct(protected CompanyInvitationService $service)
    {}

    public function invite(SendInvitationRequest $request, $id)
    {
        $user = auth()->user();

        $data = $request->validated();

        $data['role_id'] = Role::where('title', Role::MEMBER)
            ->where('scope', Role::COMPANY)
            ->value('id');

        $invitation = $this->service->sendInvitation($id, $data['role_id'], $data['email'], $user->id);

        return $this->success($invitation, 'An invitation sent successfully');

    }

    public function decline($token)
    {
        $invitation = $this->service->declineInvitation($token);

        return $this->success(null, 'Invitation declined');
    }

    public function reinvite($id)
    {
        $invitation = CompanyInvitation::findOrFail($id);

        $this->service->resendInvitation($invitation);
        
        return $this->success(
            $invitation,
            'Invitation resent successfully'
        );
    }

    public function show($token)
    {
        $invitation = CompanyInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            abort(400, 'Invitation already used.');
        }

        if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
            abort(400, 'Invitation expired.');
        }

        return response()->json([
            'email'      => $invitation->email,
            'company_id' => $invitation->company_id,
            'status'     => $invitation->status,
        ]);
    }
}
