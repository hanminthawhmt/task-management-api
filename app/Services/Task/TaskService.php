<?php
namespace App\Services\Task;

use App\Models\ProjectMember;
use App\Models\Task;
use App\Services\Permission\ProjectPermissionService;

class TaskService
{
    public function __construct(protected ProjectPermissionService $service)
    {}
    public function createTask($data, $user)
    {

        $task = Task::create([
             ...$data->validated(),
            'status'     => $data['status'] ?? "pending",
            'created_by' => $user->id,
        ]);

        return $task;

    }
    public function markAsComplete($task, $user)
    {
        $task->update(['status' => 'complete']);
        return $task->refresh();
    }

    public function getTasksForProject($project, $user)
    {
        // check if that user belongs to the project first
        $isMember = ProjectMember::where('project_id', $project->id)->where('user_id', $user->id)->exists();

        if (! $isMember) {
            throw new \Exception('Unauthorized access');
        }
        $query = Task::where('project_id', $project->id);

        if ($user->platform_role === 'super_admin' ||
            $this->service->hasPermission($user, $project, 'view_all_tasks')) {
            return $query
                ->latest()
                ->get();
        }

        if ($this->service->hasPermission($user, $project, 'view_assigned_task')) {
            return $query->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return collect();
    }
}
