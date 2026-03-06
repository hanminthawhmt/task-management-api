<?php
namespace App\Jobs;

use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendProjectInvitationEmail implements ShouldQueue
{
    use Queueable;
    use Dispatchable;

    public $invitation;
    public $acceptUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(ProjectInvitation $invitation, string $acceptUrl)
    {
        $this->invitation = $invitation;
        $this->acceptUrl  = $acceptUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->invitation->email)->send(new ProjectInvitationMail($this->invitation, $this->acceptUrl));
    }
}
