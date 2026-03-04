<?php
namespace App\Services;

use App\Models\Task;

class ProjectService
{
    public function getProjectTasks($projectId, $user)
    {
        if (in_array($user->role->title, ['admin', 'manager'])) {
            return Task::where('project_id', $projectId)->with(['assignee', 'creator'])->get();
        }

        return Task::where('project_id', $projectId)->where('user_id', $user->id)->with(['assignee', 'creator'])->get();
    }
}
