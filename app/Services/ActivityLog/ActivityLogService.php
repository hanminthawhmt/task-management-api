<?php
namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Project;

class ActivityLogService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function log($user, $action, $subject, $meta = [])
    {
        $projectId = null;
        $companyId = null;

        // Company
        if ($subject instanceof Company) {
            $companyId = $subject->id;
        }

        // Project
        if ($subject instanceof Project) {
            $companyId = $subject->company_id;
            $projectId = $subject->id;
        }

        //Models that contain project_id (Task, Invitation, etc)
        if (isset($subject->project_id)) {
            $projectId = $subject->project_id;

            if (! $companyId) {
                $project   = Project::find($projectId);
                $companyId = $project?->company_id;
            }
        }

        // Models that contain company_id
        if (isset($subject->company_id)) {
            $companyId = $subject->company_id;
        }

        ActivityLog::create([
            'company_id'   => $companyId,
            'project_id'   => $projectId,
            'user_id'      => $user->id,
            'action'       => $action,
            'subject_type' => class_basename($subject),
            'subject_id'   => $subject->id,
            'meta'         => $meta,
        ]);
    }
}
