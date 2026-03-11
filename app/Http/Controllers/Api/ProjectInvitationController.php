<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Services\Invitation\ProjectInvitationService;
use Illuminate\Http\Request;

class ProjectInvitationController extends Controller
{
    protected $service;
    public function __construct(ProjectInvitationService $invitationService)
    {
        $this->service = $invitationService;
    }

    public function invite(Request $request, $id)
    {
        $user = auth()->user();
        $data = $request->validate([
            'email' => 'required|email',
        ]);
        $data['role_id'] = Role::where('title', Role::DEVELOPER)->where('scope', Role::PROJECT)->value('id');

        $invitation = $this->service->sendInvitation($id, $data['email'], $data['role_id'], $user->id);

        return $this->success($invitation, 'An invitation sent successfully');

    }

    public function accept($token)
    {
        $user = auth()->user();

        if (! $user) {
            abort(401, "You must login first.");
        }

        $invitation = $this->service->acceptInvitation($token, $user);

        return $this->success($invitation, "Invitation accepted");
    }

    public function decline($token)
    {
        $user       = auth()->user();
        $invitation = $this->service->declineInvitation($token);

        return $this->success(null, 'Invitation declined');
    }

    public function reinvite($id)
    {
        $invitation = ProjectInvitation::findOrFail($id);

        $member = ProjectMember::where('project_id', $invitation->project_id)
            ->where('user_id', auth()->id())
            ->with('role')
            ->first();

        if (! $member || ! in_array($member->role->title, ['owner', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->service->resendInvitation($invitation);

        return $this->success(
            $invitation,
            'Invitation resent successfully'
        );
    }

    public function show($token)
    {
        $invitation = ProjectInvitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            abort(400, 'Invitation already used.');
        }

        if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
            abort(400, 'Invitation expired.');
        }

        return response()->json([
            'email'      => $invitation->email,
            'project_id' => $invitation->project_id,
        ]);
    }

    public function createProjectAndInvite(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            abort(401, "You must login first.");
        }

        $data = $request->validate([
            'project_name'        => 'required|string',
            'project_description' => 'required|string',
            'company_id'          => 'required|integer|exists:companies,id',
            'invite_emails'       => 'nullable|array',
            'invite_emails.*'     => 'email|distinct',
        ]);

        $data['created_by'] = $user->id;

        $project = $this->service->createProjectWithMember($data, $user);

        return $this->success($project, 'Project has been successfully created');
    }
}
