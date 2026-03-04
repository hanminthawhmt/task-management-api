<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $projectService;
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }
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

        $this->authorize('create', Project::class);

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
        $project = Project::findOrFail($id);

        $this->authorize('viewProjectTasks', Project::class);

        $tasks = $this->projectService->getProjectTasks($project->id, auth()->user());

        return $this->success(
            TaskResource::collection($tasks),
            'Project tasks retrieved successfully'
        );

    }
}
