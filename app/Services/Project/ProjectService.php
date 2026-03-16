<?php
namespace App\Services\Project;

use App\Jobs\SendProjectInvitationEmail;
use App\Models\CompanyMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProjectService
{
    public function createProject($data, $companyId, $user)
    {
        return DB::transaction(function () use ($data, $companyId, $user) {

            $project = Project::create([
                'title'       => $data['title'],
                'description' => $data['description'],
                'created_by'  => $user->id,
                'company_id'  => $companyId,
            ]);

            $roleId = Role::where('title', Role::OWNER)->where('scope', Role::PROJECT)->value('id');

            $project->members()->create([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);

            if (! empty($data['invite_emails'])) {
                $this->processInvitations($project, $data['invite_emails'], $user, $companyId);
            }

            return $project->refresh()->load('invitations');

        });
    }

    protected function processInvitations($project, $emails, $user, $companyId)
    {
        $defaultRole = Role::where('title', Role::DEVELOPER)->where('scope', Role::PROJECT)->first();

        foreach ($emails as $email) {
            $existingEmployee = CompanyMember::where('company_id', $companyId)->whereHas('user', fn($q) => $q->where('email', $email))->exists();

            if (! $existingEmployee) {
                continue;
            }

            $token = Str::uuid();

            $invitation = $project->invitations()->create([
                'email'      => $email,
                'role_id'    => $defaultRole->id,
                'token'      => $token,
                'invited_by' => $user->id,
                'status'     => 'pending',
                'expires_at' => now()->addDays(3),
            ]);

            $acceptUrl = URL::temporarySignedRoute(
                'invitation.accept',
                now()->addDays(3),
                ['token' => $token]
            );

            \Log::info('Invitation signed URL: ' . $acceptUrl);

            SendProjectInvitationEmail::dispatch($invitation, $acceptUrl)->afterCommit();

        }

    }

    public function addMember($project, $userId, $roleId)
    {
        return ProjectMember::create([
            'project_id' => $project->id,
            'user_id'    => $userId,
            'role_id'    => $roleId,
        ]);
    }

    public function getUserProjects($user)
    {
        return $user->projects()->with('creator')->get();
    }

    public function getProject($id, $user)
    {
        return Project::forCurrentCompany()->where('id', $id)->with(['creator', 'members.user', 'members.role'])->findOrFail($id);
    }

    public function getProjectTasks($project, $user)
    {
        $member = ProjectMember::where('project_id', $project->id)->where('user_id', $user->id)->with('role')->first();

        if (! $member) {
            abort(403, 'Not a member of this project');
        }

        if (in_array($member->role->title, [Role::OWNER, Role::MANAGER])) {
            return Task::where('project_id', $project->id)->with(['assignee', 'creator'])->latest()->get();
        }

        return Task::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->with(['assignee', 'creator'])
            ->latest()
            ->get();

    }

}
