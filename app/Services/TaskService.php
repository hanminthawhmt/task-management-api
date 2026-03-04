<?php
namespace App\Services;

class TaskService
{
    

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
