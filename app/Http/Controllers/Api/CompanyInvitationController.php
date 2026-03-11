<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Services\Invitation\CompanyInvitationService;
use Illuminate\Http\Request;

class CompanyInvitationController extends Controller
{
    public function __construct(protected CompanyInvitationService $service)
    {}

    public function invite(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'email'      => 'required|email',
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $data['role_id'] = Role::where('title', Role::MEMBER)
            ->where('scope', Role::COMPANY)
            ->value('id');

        $invitation = $this->service->sendInvitation($data['company_id'], $data['role_id'], $data['email'], $user->id);

        return $this->success($invitation, 'An invitation sent successfully');

    }

    public function accept()
    {

    }

    public function decline()
    {

    }

    public function resend()
    {

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
