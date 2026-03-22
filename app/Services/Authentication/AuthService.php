<?php
namespace App\Services\Authentication;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyMember;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{

    public function registerAsAdmin($request): array
    {
        $request['password']      = Hash::make($request['password']);
        $request['platform_role'] = 'super_admin';

        $user = User::create($request);

        $token = Auth::login($user);

        return [
            'user'  => $user,
            'token' => $token,
        ];

    }

    public function registration($data)
    {
        return DB::transaction(function () use ($data) {

            $data['password']      = Hash::make($data['password']);
            $data['platform_role'] = 'user';

            $invitation = null;

            if (! empty($data['invitation_token'])) {
                $invitation = CompanyInvitation::where('token', $data['invitation_token'])->firstOrFail();

                if ($invitation->status !== 'pending') {
                    throw new \Exception('Invitation already used');
                }

                if ($invitation->email !== $data['email']) {
                    throw new \Exception('Invitation email mismatch.');
                }

                if ($invitation->expires_at && now()->gt($invitation->expires_at)) {
                    throw new \Exception("Invitation expired.");
                }
            }

            $user = User::create($data);

            if ($invitation) {
                $company = Company::findOrFail($invitation->company_id);

                $membership = CompanyMember::create([
                    'company_id' => $company->id,
                    'user_id'    => $user->id,
                    'role_id'    => Role::where('title', Role::MEMBER)
                        ->where('scope', Role::COMPANY)
                        ->value('id'),
                ]);

                $invitation->update([
                    'status'      => 'accepted',
                    'accepted_at' => NOW(),

                ]);

            } else {
                // normal company registration
                $company = Company::create([
                    'name'       => $data['company_name'],
                    'created_by' => $user->id,
                ]);

                $membership = CompanyMember::create([
                    'company_id' => $company->id,
                    'user_id'    => $user->id,
                    'role_id'    => Role::where('title', Role::OWNER)->
                        where('scope', Role::COMPANY)
                        ->value('id'),
                ]);

                $plan = Plan::findOrFail($data['plan_id']);

                Subscription::create([
                    'company_id' => $company->id,
                    'plan_id'    => $plan->id,
                    'starts_at'  => NOW(),
                ]);

            }
            $token = Auth::login($user);
            return [
                'user'  => $user,
                'token' => $token,
            ];
        });
    }

    public function login($request): array
    {
        $token = Auth::attempt($request);
        if (! $token) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }

        return [
            'user'  => Auth::user(),
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function refresh(): array
    {
        return [
            'user'  => Auth::user(),
            'token' => Auth::refresh(),
        ];
    }
}
