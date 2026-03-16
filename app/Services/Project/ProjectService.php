<?php
namespace App\Services\Project;

use App\Jobs\SendProjectInvitationEmail;
use App\Models\CompanyMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
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

    public function getUserProjects($user)
    {
        return $user->projects()->with('creator')->get();
    }

    public function getProject($id, $user)
    {
        return Project::forCurrentCompany()->where('id', $id)->with(['creator', 'members.user', 'members.role'])->findOrFail($id);
    }

}
