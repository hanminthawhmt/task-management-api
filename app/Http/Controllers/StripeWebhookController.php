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
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'customer.subscription.created': // 👈 ADD THIS
                $this->handleSubscriptionCreated($event->data->object);
                break;

            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancelled($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutCompleted($session)
    {
        $companyId = $session->metadata->company_id ?? null;

        if (! $companyId) {
            return;
        }

        $company = Company::find($companyId);

        if (! $company) {
            return;
        }

        // Optional: mark as processing (NOT active yet)
        $company->update([
            'subscription_status' => 'processing',
        ]);
    }
    protected function handleInvoicePaid($invoice)
    {
        $subscription = Subscription::where('stripe_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'active',
            ]);
        }
    }

    protected function handleSubscriptionCreated($stripeSub)
    {
        try {
            $stripePrice = $stripeSub->items->data[0]->price->id;

            $plan = Plan::where('stripe_price_id', $stripePrice)->first();

            if (! $plan) {
                \Log::error('Plan not found for price: ' . $stripePrice);
                return;
            }

            $companyId = $stripeSub->metadata->company_id ?? null;

            if (! $companyId) {
                \Log::error('No company_id in subscription metadata');
                return;
            }

            $company = Company::find($companyId);

            if (! $company) {
                \Log::error('Company not found: ' . $companyId);
                return;
            }

            // Prevent duplicate
            $exists = Subscription::where('stripe_id', $stripeSub->id)->exists();
            if ($exists) {
                return;
            }

            // ✅ CREATE subscription (ONLY HERE)
            $company->subscriptions()->create([
                'type'          => 'default',
                'stripe_id'     => $stripeSub->id,
                'stripe_status' => $stripeSub->status,
                'stripe_price'  => $stripePrice,
                'plan_id'       => $plan->id,
            ]);

            // ✅ NOW mark company active
            $company->update([
                'subscription_status' => 'active',
            ]);

        } catch (\Exception $e) {
            \Log::error('Subscription Create Error: ' . $e->getMessage());
        }
    }

    protected function handleSubscriptionUpdated($stripeSub)
    {
        $stripePrice = $stripeSub->items->data[0]->price->id;

        $plan = Plan::where('stripe_price_id', $stripePrice)->first();

        $subscription = Subscription::where('stripe_id', $stripeSub->id)->first();

        if ($subscription && $plan) {
            $subscription->update([
                'plan_id' => $plan->id,
            ]);
        }
    }

    protected function handleSubscriptionCancelled($stripeSub)
    {
        $subscription = Subscription::where('stripe_id', $stripeSub->id)->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'cancelled',
            ]);
        }
    }
}
