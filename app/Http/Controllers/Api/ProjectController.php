<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Admin -> can create projects
    // Manager -> can create projects
    public function store(StoreProjectRequest $request)
    {

        $user = auth()->user();
        $role = $user->role->title;

        if (! in_array($role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unathorized',
            ], 403);
        }

        $data               = $request->validated();
        $data['created_by'] = $user->id;

        $project = Project::create($data);

        return $this->success(new ProjectResource($project), 'A project has been successfully created');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Admin -> can see all of the assigned task list from the projects
    // Manager -> can see all of the assigned task list from the projects
    // Member -> can see only their task from the projects
    public function getProjectTasks($id)
    {
        $user = auth()->user();
        $role = $user->role->title;

        $project = Project::findOrFail($id);

        if (in_array($role, ['admin', 'manager'])) {
            $tasks = Task::where('project_id', $id)->with(['assignee', 'creator'])->get();
        } else {
            $tasks = Task::where('project_id', $id)->where('user_id', $user->id)->with(['assignee', 'creator'])->get();
        }

        return $this->success(
            TaskResource::collection($tasks),
            'Project tasks retrieved successfully'
        );

    }
}
