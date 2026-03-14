<?php
namespace App\Services\Task;

use App\Models\Task;

class TaskService
{
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
        if ($user->role->title === 'member' && $task->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unathorized',
            ]);
        }

        $task->update(['status' => 'complete']);
        return $task->refresh();
    }
}
