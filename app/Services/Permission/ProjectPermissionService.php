<?php
namespace App\Services\Permission;

use App\Models\CompanyMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProjectPermissionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function hasPermission(User $user, Project $project, string $permission)
    {
        if ($user->platform_role === 'super_admin') {
            return true;
        }

        $companyMember = CompanyMember::where('company_id', $project->company_id)
            ->where('user_id', $user->id)
            ->with('role')
            ->first();

        if ($companyMember && $companyMember->role->title === 'Owner') {
            return true;
        }

        $cacheKey = "project_permissions_{$user->id}_{$project->id}";

        $permissions = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($user, $project) {

            $membership = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->with('role.permissions')->first();

            if (! $membership) {
                return [];
            }

            return $membership->role->permissions
                ->pluck('name')
                ->toArray();
        });

        return in_array($permission, $permissions);
    }

    public function clearProjectPermissionCache($userId, $projectId)
    {
        Cache::forget("project_permissions_{$userId}_{$projectId}");
    }
    
}
