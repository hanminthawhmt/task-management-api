<?php
namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class BillingController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'plan_id'    => 'required|exists:plans,id',
            'company_id' => 'required|exists:companies,id',
        ]);

        $company = $user->companies()
            ->where('company_id', $request->company_id)->firstOrFail();

        $plan = Plan::findOrFail($request->plan_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode'                 => 'subscription',
            'payment_method_types' => ['card'],
            'customer_email'       => $user->email,
            'line_items'           => [[
                'price'    => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url'          => 'http://localhost/task-management-api/public/success',
            'cancel_url'           => 'http://localhost/task-management-api/public/cancel',
            'metadata'             => [
                'company_id' => $company->id,
                'plan_id'    => $plan->id,
            ],
        ]);

        return response()->json([
            'url' => $session->url,
        ]);

        // return redirect($session->url);

    }
}
