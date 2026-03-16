<?php
namespace App\Services\Task;

use App\Models\ProjectMember;
use App\Models\Task;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Permission\ProjectPermissionService;

class TaskService
{
    public function __construct(protected ProjectPermissionService $service, protected ActivityLogService $logService)
    {}
    public function createTask($data, $user)
    {

        $task = Task::create([
             ...$data->validated(),
            'status'     => $data['status'] ?? "pending",
            'created_by' => $user->id,
        ]);

        $this->logService->log($user, 'created_a_task', $task);

        return $task;

    }

    public function markAsComplete($task, $user)
    {
        $task->update(['status' => 'complete']);
        $this->logService->log($user, 'mark_the_task_as_complete', $task);
        return $task->refresh();
    }

    public function toggleStatus($task, $user)
    {
        $oldStatus = $task->status;
        $newStatus = $oldStatus === 'pending' ? 'complete' : 'pending';
        $task->update(['status' => $newStatus]);
        $this->logService->log($user, 'update_the_task_status', $task, ["status" => $newStatus]);
        return $task;
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
