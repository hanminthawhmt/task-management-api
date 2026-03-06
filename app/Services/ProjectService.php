<?php
namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ProjectService
{

    public function createProject($request, $user)
    {
        $data               = $request->validated();
        $data['created_by'] = $user->id;

        return DB::transaction(function () use ($data, $user) {
            $project = Project::create($data);

            ProjectMember::create([
                "project_id" => $project->id,
                "user_id"    => $user->id,
                'role_id'    => Role::where('title', Role::OWNER)->value('id'),
            ]);

            return $project;
        });
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

    public function getProject($id)
    {
        return Project::with(['creator', 'members.user', 'members.role'])->findOrFail($id);
    }

    public function getProjectTasks($project, $user)
    {
        $member = ProjectMember::where('project_id', $project->id)->where('user_id', $user->id)->with('role')->first();

        if (! $member) {
            abort(403, 'Not a member of this project');
        }

        if (! in_array($member->role->title, ['owner', 'manager'])) {
            return Task::where('project_id', $project->id)->with(['assignee', 'creator'])->latest()->get();
        }

        return Task::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->with(['assignee', 'creator'])
            ->latest()
            ->get();

    }

}
