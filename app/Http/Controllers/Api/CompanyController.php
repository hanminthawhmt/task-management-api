<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;

class CompanyController extends Controller
{
    public function __construct(protected CompanyService $service)
    {}

    public function index()
    {
        $companies = $this->service->getAll(auth()->user());
        return $this->success($companies, 'Companies List Retrieved Successfully');
    }
}
