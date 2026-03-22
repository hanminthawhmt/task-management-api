<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );

        switch ($event->type) {

            case 'checkout.session.completed':

                $session = $event->data->object;

                $companyId = $session->metadata->company_id;
                $planId    = $session->metadata->plan_id;

                $company = Company::find($companyId);

                // Create subscription in Cashier
                $company->subscriptions()->create([
                    'type'          => 'default',
                    'stripe_id'     => $session->subscription,
                    'stripe_status' => 'active',
                    'plan_id'       => $planId,
                ]);

                break;

            case 'customer.subscription.updated':

                $subscription = $event->data->object;

                $stripePrice = $subscription->items->data[0]->price->id;

                $plan = Plan::where('stripe_price_id', $stripePrice)->first();

                $sub = Subscription::where('stripe_id', $subscription->id)->first();

                if ($sub) {
                    $sub->update([
                        'plan_id' => $plan?->id,
                    ]);
                }

                break;
        }

        return response()->json(['status' => 'success']);
    }
}
