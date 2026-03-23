<?php
namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

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
            ->where('company_id', $request->company_id)
            ->firstOrFail();

        $plan = Plan::findOrFail($request->plan_id);

        if ($plan->is_free) {
            return response()->json([
                'message' => 'Free plan does not require checkout',
            ], 400);
        }

        $checkout = $company
            ->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url'       => 'http://localhost/task-management-api/public/api/success',
                'cancel_url'        => 'http://localhost/task-management-api/public/api/cancel',

                'metadata'          => [
                    'company_id' => $company->id,
                    'plan_id'    => $plan->id,
                ],

                'subscription_data' => [
                    'metadata' => [
                        'company_id' => $company->id,
                    ],
                ],
            ]);

        return response()->json([
            'url' => $checkout->url,
        ]);
    }
}
