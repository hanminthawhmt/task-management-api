<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Services\Invitation\CompanyInvitationService;
use Illuminate\Http\Request;

class CompanyInvitationController extends Controller
{
    public function __construct(protected CompanyInvitationService $service)
    {}

    public function invite(Request $request, Company $company)
    {
        $user = auth()->user();

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $data['role_id'] = Role::where('title', Role::MEMBER)
            ->where('scope', Role::COMPANY)
            ->value('id');

        $invitation = $this->service->sendInvitation($company, $data['role_id'], $data['email'], $user->id);

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
}
