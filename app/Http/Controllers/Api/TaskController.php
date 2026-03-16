<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\Task\TaskService;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $tasks = $this->taskService->getTasksForProject($project, auth()->user());

        return $this->success($tasks, 'Tasks retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    // user who has permissions of create_task can create a task
    public function store(StoreTaskRequest $request)
    {
        $user = auth()->user();

        $task = $this->taskService->createTask($request, $user);

        return $this->success($task, 'A task has been successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        return $this->success(new TaskResource($task), 'Task retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $id)
    {
        $user = auth()->user();
        $role = $user->role->title;
        $task = Task::find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        if (! in_array($role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $task->update($request->validated());
        return $this->success(new TaskResource($task), 'Task updated successfully');
    }

    // Quick Action
    // Admin can mark the assignee task as complete
    // Manager can mark the assignee task as complete
    // Assignee can only mark their assigned task
    public function markAsComplete($id)
    {
        $user = auth()->user();
        $task = Task::findOrFail($id);

        $updateTask = $this->taskService->markAsComplete($task, $user);
        return $this->success(
            new TaskResource($updateTask),
            'Task marked as completed'
        );
    }

    // Quick Action
    public function updateStatus($id)
    {
        $user = auth()->user();
        $task = Task::findOrFail($id);
        $newStatus = $task->status === 'pending' ? 'complete' : 'pending';
        $task->update(['status' => $newStatus]);
        return $this->success(new TaskResource($task), "Task status changed to {$newStatus}");
    }

    /**
     * Remove the specified resource from storage.
     */
    // user who has the permission of delete_task can delete a task
    public function destroy(Task $task)
    {
        $user = auth()->user();
        $task->delete();
        return $this->success(null, 'Task deleted successfully.', 200);
    }
}
