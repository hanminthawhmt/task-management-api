<?php
namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Project;

class ActivityController extends Controller
{
    public function projectFeed(Project $project)
    {
        $activities = ActivityLog::with('user')
            ->where('project_id', $project->id)
            ->latest()
            ->paginate(20);
        return ActivityResource::collection($activities);

    }

    public function companyFeed(Company $company)
    {
        $activities = ActivityLog::with('user')
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(20);

        return ActivityResource::collection($activities);
    }
}
