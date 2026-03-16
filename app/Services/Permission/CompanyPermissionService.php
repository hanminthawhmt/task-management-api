<?php
namespace App\Services\Permission;

use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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

        // Caching Layer

        $cacheKey = "company_permissions_{$user->id}_{$company->id}";

        // Cache::remember look for a cacheKey,
        // If found, it uses that data for 1 hour (3600 seconds) without touching the database.
        // If not found, it runs the code inside the function
        $permissions = Cache::remember($cacheKey, 3600, function () use ($user, $company) {
            $membership = CompanyMember::where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->with(['role.permissions'])
                ->first();

            if (! $membership) {
                return [];
            }

            return $membership->role->permissions
                ->pluck('name')
                ->toArray();
        });

        return in_array($permission, $permissions);
    }

    public function clearCompanyPermissionCache($userId, $companyId)
    {
        Cache::forget("company_permissions_{$userId}_{$companyId}");
    }
}
