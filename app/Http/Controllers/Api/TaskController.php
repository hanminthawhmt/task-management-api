<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::latest()->get();
        return $this->success(new TaskCollection($tasks), 'Task list retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = $data['status'] ?? 'pending';
        $task           = Task::create($data);
        return $this->success(new TaskResource($task), 'A task has been successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $this->success(new TaskResource($task), 'Task retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();
        $task->update($data);
        return $this->success(new TaskResource($task), 'Task updated successfully');
    }

    public function markAsComplete(Task $task)
    {
        $task->update(['status' => 'complete']);
        return $this->success(new TaskResource($task->refresh()), 'Task mark as completed');
    }

    public function updateStatus($id)
    {
        $task      = Task::findOrFail($id);
        $newStatus = ($task->status === 'pending') ? 'complete' : 'pending';
        $task->update(['status' => $newStatus]);
        return $this->success(new TaskResource($task), 'Task status changed to $newStatus');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return $this->success(null, 'Task deleted successfully.', 200);
    }
}
