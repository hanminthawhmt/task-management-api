<?php
namespace App\Services\Company;

use App\Models\Company;

class CompanyService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAll($user)
    {
        if ($user->platform_role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        return Company::with('projects', 'projects.tasks', 'members.user')->get();

        //return Company::with('projects', 'users', 'members.users')->get();
    }
}
