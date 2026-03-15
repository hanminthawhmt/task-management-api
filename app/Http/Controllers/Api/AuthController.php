<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UserLogInRequest;
use App\Http\Requests\UserOnboardingRequest;
use App\Http\Resources\UserResource;
use App\Services\Authentication\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $service)
    {

    }

    // platform admin registration
    public function registerAsAdmin(StoreAdminRequest $request)
    {
        $result = $this->service->registerAsAdmin($request->validated());

        return response()->json([
            'status'        => 'success',
            'message'       => 'User created successfully',
            'user'          => $result['user'],
            'authorisation' => [
                'token' => $result['token'],
                'type'  => 'bearer',
            ],
        ]);
    }

    //support register as a company owner or register from company invitation
    public function register(UserOnboardingRequest $request)
    {
        $data = $request->validated();

        $result = $this->service->registration($data);

        return response()->json([
            'status'        => 'success',
            'message'       => 'User created successfully',
            'user'          => new UserResource($result['user']),
            'authorisation' => [
                'token' => $result['token'],
                'type'  => 'bearer',
            ],
        ]);
    }

    public function login(UserLogInRequest $request)
    {

        $credentials = $request->validated();

        $result = $this->service->login($credentials);

        return response()->json([
            'status'        => 'success',
            'user'          => new UserResource($result['user']),
            'authorisation' => [
                'token' => $result['token'],
                'type'  => 'bearer',
            ],
        ]);

    }

    public function logout()
    {
        $this->service->logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        $result = $this->service->refresh();

        return response()->json([
            'status'        => 'success',
            'user'          => $result['user'],
            'authorisation' => [
                'token' => $result['token'],
                'type'  => 'bearer',
            ],
        ]);
    }

}
