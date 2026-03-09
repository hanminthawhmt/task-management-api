<?php
namespace App\Services\Permissions;

use App\Models\CompanyMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

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

        $membership = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->with('role.permissions')->first();

        if (! $membership) {
            return false;
        }

        return $membership->role->permissions
            ->contains('name', $permission);
    }
}
