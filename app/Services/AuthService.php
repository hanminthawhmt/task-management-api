<?php
namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function registration($data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            $company = Company::create([
                'name'       => $data['company_name'],
                'created_by' => $user->id,
            ]);

            $user->update([
                'company_id'   => $company->id,
                'company_role' => 'owner',
            ]);

            return $user;
        });
    }
}
