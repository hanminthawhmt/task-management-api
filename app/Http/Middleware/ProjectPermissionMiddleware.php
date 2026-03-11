<?php
namespace App\Http\Middleware;

use App\Models\Project;
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

        $projectId = $request->route('project_id') ?? $request->input('project_id');

        $project = Project::findOrFail($projectId);

        if (! $this->service->hasPermission($user, $project, $permission)) {
            return response()->json([
                'message' => 'Forbidden: insufficient project permission',
            ], 403);
        }

        return $next($request);
    }
}
