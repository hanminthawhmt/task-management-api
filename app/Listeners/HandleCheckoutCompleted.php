<?php
namespace App\Listeners;

use App\Models\Company;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Subscription;

class HandleCheckoutCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event)
    {
        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'];

        $companyId = $session['metadata']['company_id'] ?? null;
        $planId    = $session['metadata']['plan_id'] ?? null;

        if (! $companyId) {
            return;
        }

        $company = Company::find($companyId);

        if (! $company) {
            return;
        }

        $company->update([
            'subscription_status' => 'active',
        ]);

        $subscription = Subscription::where('stripe_id', $session['subscription'])->first();

        if ($subscription && $planId) {
            $subscription->update([
                'plan_id' => $planId,
            ]);
        }
    }
}
