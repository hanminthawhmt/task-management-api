<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectInvitation;
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

    public function invite(Request $request, Project $project)
    {

        $data = $request->validate([
            'email'   => 'required|email',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        // if the role is not set by the project owner, it will be the default role which is developer
        $roleId = $data['role_id'] ?? Role::where('title', Role::DEVELOPER)
            ->where('scope', Role::PROJECT)
            ->value('id');

        $invitation = $this->service->sendInvitation($project->id, $data['email'], $roleId, auth()->user());

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

    public function reinvite(Project $project, ProjectInvitation $invitation)
    {
        $invitation = ProjectInvitation::where('project_id', $project->id)->findOrFail($invitation->id);

        $this->service->resendInvitation($invitation, auth()->user());

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
}
