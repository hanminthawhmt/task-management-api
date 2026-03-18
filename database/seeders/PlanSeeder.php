<?php
namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name'         => 'Free',
            'price'        => 0,
            'max_projects' => 3,
        ]);

        Plan::create([
            'name'         => 'Premium',
            'price'        => 1000, // $10
            'max_projects' => null, // unlimited
        ]);
    }
}
