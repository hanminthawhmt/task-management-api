<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $username = $this->user?->name ?? 'Unknown user';

        $message = match ($this->action) {

            'created_project' => "{$username} created project.",
            'invited_project_member' => "{$username} invited " . ($this->meta['email'] ?? 'a new member') . " to the project.",
            'invited_company_member' => "{$username} invited " . ($this->meta['email'] ?? 'a guest') . " to the company workspace.",
            'created_task' => "{$username} created a task.",
            'mark_the_task_as_complete' => "{$username} marked the task as completed.",
            'update_the_task_status' => "{$username} updated the task status",
            default => "{$username} performed {$this->action}"
            
        };

        return [
            "id"           => $this->id,
            "message"      => $message,
            "action"       => $this->action,
            "user"         => $this->user?->name,
            "subject_type" => $this->subject_type,
            "subject_id"   => $this->subject_id,
            "meta"         => $this->meta,
            "created_at"   => $this->created_at->diffForHumans(),
        ];
    }
}
