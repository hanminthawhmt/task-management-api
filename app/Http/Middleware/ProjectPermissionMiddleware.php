<?php
namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Task;
use App\Services\Permission\ProjectPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectPermissionMiddleware
{
    public function __construct(protected ProjectPermissionService $service)
    {}
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $project = $this->resolveProject($request);

        if (! $this->service->hasPermission($user, $project, $permission)) {
            return response()->json([
                'message' => 'Forbidden: insufficient project permission',
            ], 403);
        }

        return $next($request);
    }

    private function resolveProject(Request $request): Project
    {
        $projectParam = $request->route('project') ?? $request->route('id') ?? $request->input('project_id');

        if ($projectParam) {
            return $projectParam instanceof Project ? $projectParam : Project::findOrFail($projectParam);
        }

        $taskParam = $request->route('task');

        if ($taskParam) {
            $task = $taskParam instanceof Task ? $taskParam : Task::findOrFail($taskParam);
            return $task->project;
        }

        abort(400, 'Unable to determine project.');
    }
}
