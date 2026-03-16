<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\TaskResource;
use App\Models\Company;
use App\Models\Project;
use App\Services\Project\ProjectService;
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
    public function store(StoreProjectRequest $request, Company $company)
    {
        //$this->authorize('create', Project::class);

        $project = $this->projectService->createProject($request->validated(), $company->id, auth()->user());

        return $this->success($project, 'A project has been successfully created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user    = auth()->user();
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
}
