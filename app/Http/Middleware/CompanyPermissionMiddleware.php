<?php
namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\Permission\CompanyPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyPermissionMiddleware
{
    public function __construct(protected CompanyPermissionService $service)
    {}
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        // get current user
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $companyParameter = $request->route('company') ?? $request->route('id') ?? $request->input('company_id');

        $company = $companyParameter instanceof Company ? $companyParameter : Company::findOrFail($companyParameter);

        if (! $this->service->hasPermission($user, $company, $permission)) {
            return response()->json([
                'message' => 'Forbidden: insufficient company permission',
            ], 403);
        }

        return $next($request);
    }
}
