<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        $company = $user->companyMemberships()->first()->company;

        if ($company->subscription_status === 'pending') {
            return response()->json([
                'message' => 'Please complete payment to continue.',
            ], 402);
        }

        return $next($request);
    }
}
