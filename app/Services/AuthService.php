<?php
namespace App\Services;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function registration($data)
    {
        return DB::transaction(function () use ($data) {

            $invitation = null;
            if (! empty($data['invitation_token'])) {
                $invitation = ProjectInvitation::where('token', $data['invitation_token'])->firstOrFail();

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
                // Join existing company
                $project = Project::findOrFail($invitation->project_id);

                $user->update([
                    'company_id'   => $project->company_id,
                    'company_role' => 'member',
                ]);

                ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id'    => $user->id,
                    'role_id'    => $invitation->role_id,
                ]);

                $invitation->update([
                    'status'      => 'accepted',
                    'accepted_at' => now(),
                ]);
            } else {
                // normal company registration
                $company = Company::create([
                    'name'       => $data['company_name'],
                    'created_by' => $user->id,
                ]);

                $user->update([
                    'company_id'   => $company->id,
                    'company_role' => 'owner',
                ]);
            }

            return $user;
        });
    }
}
