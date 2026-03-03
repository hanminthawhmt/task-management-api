<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Admin -> see all tasks
    // Manager -> see all tasks
    // Member -> see only assigned tasks
    public function index()
    {
        // get the current authenticated user
        $user = auth()->user();
        // get the user'role
        $role = $user->role->title;

        if ($role === 'admin' || $role === 'manager') {
            return Task::with(['assignee', 'creator'])->get();
        }

        return Task::with(['assignee', 'creator'])->where('user_id', $user->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    // Admin -> can assign tasks
    // Manager -> can assign tasks
    // Member -> cannot assign tasks
    public function store(StoreTaskRequest $request)
    {
        $user = auth()->user();
        $role = $user->role->title;

        if (! in_array($role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $task = Task::create([
             ...$request->validated(),
            'status'     => $request->status ?? "pending",
            'created_by' => $user->id,
        ]);

        return $this->success(new TaskResource($task), 'A task has been successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        return $this->success(new TaskResource($task), 'Task retrieved successfully.');
    }

    // Admin -> can update anything
    // Manager -> can update anything
    // Member -> can update the assigned task (status of the task)
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

    public function markAsComplete($id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        $task->update(['status' => 'complete']);
        return $this->success(new TaskResource($task->refresh()), 'Task mark as completed');
    }

    public function updateStatus($id)
    {
        $task      = auth()->user()->tasks()->findOrFail($id);
        $newStatus = ($task->status === 'pending') ? 'complete' : 'pending';
        $task->update(['status' => $newStatus]);
        return $this->success(new TaskResource($task), "Task status changed to {$newStatus}");
    }

    /**
     * Remove the specified resource from storage.
     */
    // Admin -> can delet task
    // Manager -> cannot delet task
    // Member -> cannot delet task
    public function destroy($id)
    {
        $user = auth()->user();
        $task = Task::findOrFail($id);

        $role = $user->role->title;
        if ($role !== 'admin') {
            return response()->json([
                "message" => "Unauthorized",
            ], 403);
        }

        $task->delete();
        return $this->success(null, 'Task deleted successfully.', 200);
    }

    // Admin -> can see the list of specific user tasks
    // Manager -> can see the list of specfic user tasks
    public function getUserTasks($id)
    {
        $user = auth()->user();
        $role = $user->role->title;

        if (! in_array($role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $tasks = Task::where('user_id', $id)->with(['assignee', 'creator'])->get();

        return $this->success(TaskResource::collection($tasks), 'User tasks retrieved successfully');
    }
}
