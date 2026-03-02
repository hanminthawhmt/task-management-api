<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = auth()->user()->tasks()->latest()->get();
        return $this->success(new TaskCollection($tasks), 'Task list retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {

        $task = auth()->user()->tasks()->create([
             ...$request->validated(),
            'status' => $request->status ?? 'pending',
        ]);
        return $this->success(new TaskResource($task), 'A task has been successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //$task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $task = auth()->user()->tasks()->findOrFail($id);
        return $this->success(new TaskResource($task), 'Task retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, $id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
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
    public function destroy($id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        $task->delete();
        return $this->success(null, 'Task deleted successfully.', 200);
    }
}
