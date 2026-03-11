<?php
namespace App\Services\Permission;

use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;

class CompanyPermissionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public function hasPermission(User $user, Company $company, string $permission)
    {
        if ($user->platform_role === 'super_admin') {
            return true;
        }

        $membership = CompanyMember::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->with(['role.permissions'])
            ->first();

        if (! $membership) {
            return false;
        }

        return $membership->role->permissions->contains('name', $permission);
    }
}
