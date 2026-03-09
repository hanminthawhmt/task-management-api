<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            // Company Roles
            ['title' => 'Owner', 'scope' => 'company'],
            ['title' => 'Admin', 'scope' => 'company'],
            ['title' => 'Manager', 'scope' => 'company'],
            ['title' => 'Member', 'scope' => 'company'],
            ['title' => 'Guest', 'scope' => 'company'],

            // Project Roles
            ['title' => 'Owner', 'scope' => 'project'],
            ['title' => 'Manager', 'scope' => 'project'],
            ['title' => 'Developer', 'scope' => 'project'],
            ['title' => 'Viewer', 'scope' => 'project'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
