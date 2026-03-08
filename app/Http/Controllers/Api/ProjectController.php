<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
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
        $projects = $this->projectService->getUserProjects(auth()->user());

        return $this->success($projects, 'Projects retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    // CREATE a project
    // The one who creates the project becomes the project owner
    public function store(StoreProjectRequest $request)
    {

        //$this->authorize('create', Project::class);

        $project = $this->projectService->createProject($request, auth()->user());

        return $this->success($project, 'A project has been successfully created');

    }

    // Add members to the project
    public function addMember(Request $request, $projectId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $project = Project::findOrFail($projectId);
        $member  = $this->projectService->addMember($project, $request->user_id, $request->role_id);

        return $this->success($member, 'Member added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $project = $this->projectService->getProject($id, $user);
        return $this->success($project, 'Project retrieved successfully');
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

        // $this->authorize('viewProjectTasks', Project::class);

        $tasks = $this->projectService->getProjectTasks($project, auth()->user());

        return $this->success(
            TaskResource::collection($tasks),
            'Project tasks retrieved successfully'
        );

    }

}
