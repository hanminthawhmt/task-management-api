<?php
namespace App\Jobs;

use App\Mail\CompanyInvitationMail;
use App\Models\CompanyInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendCompanyInvitationEmail implements ShouldQueue
{
    use Queueable;
    use Dispatchable;

    public $invitation;
    public $acceptUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(CompanyInvitation $invitation, string $acceptUrl)
    {
        $this->invitation = $invitation;
        $this->acceptUrl  = $acceptUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->invitation->email)->send(new CompanyInvitationMail($this->invitation, $this->acceptUrl));
    }
}
