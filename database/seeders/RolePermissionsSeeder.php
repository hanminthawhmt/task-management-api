<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner   = Role::where('title', 'Owner')->where('scope', 'company')->first();
        $admin   = Role::where('title', 'Admin')->where('scope', 'company')->first();
        $manager = Role::where('title', 'Manager')->where('scope', 'company')->first();
        $member  = Role::where('title', 'Member')->where('scope', 'company')->first();

        $permissions = Permission::pluck('id');

        // Owner gets everything
        $owner->permissions()->sync($permissions);

        // Admin
        $adminPermissions = Permission::whereIn('name', [
            'create_project',
            'invite_company_member',
            'remove_company_member',
        ])->pluck('id');

        $admin->permissions()->sync($adminPermissions);

        // Manager
        $managerPermissions = Permission::whereIn('name', [
            'create_project',
            'invite_company_member',
        ])->pluck('id');

        $manager->permissions()->sync($managerPermissions);

        // Member
        $memberPermissions = Permission::whereIn('name', [
            'view_project',
        ])->pluck('id');

        $member->permissions()->sync($memberPermissions);
    }
}
