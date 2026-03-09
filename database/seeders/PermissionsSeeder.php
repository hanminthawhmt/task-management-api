<?php
namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Company permissions
            'create_project',
            'delete_project',
            'invite_company_member',
            'remove_company_member',
            'update_company_settings',

            // Project permissions
            'invite_project_member',
            'remove_project_member',
            'update_project_settings',

            // Task permissions
            'create_task',
            'update_task',
            'delete_task',
            'view_project',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}
