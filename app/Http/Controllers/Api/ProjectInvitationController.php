<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Services\ProjectInvitationService;
use Illuminate\Http\Request;

class ProjectInvitationController extends Controller
{
    protected $service;
    public function __construct(ProjectInvitationService $invitationService)
    {
        $this->service = $invitationService;
    }

    public function invite(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'email'      => 'required|email',
            'role_id'    => 'required|exists:roles,id',
        ]);

        $project = Project::findOrFail($request->project_id);

        $invitation = $this->service->sendInvitation($project->id, $request->email, $request->role_id, $user->id);

        return $this->success($invitation, 'An invitation sent successfully');

    }

    public function accept($token)
    {
        $user = auth()->user();

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
}
