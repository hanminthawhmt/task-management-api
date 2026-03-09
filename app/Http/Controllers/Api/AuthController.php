<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UserLogInRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected AuthService $service)
    {

    }

    //TODO: need to refactor into serivce, controller, request, resource
    public function registerAsAdmin(Request $request)
    {
        $data = $request->validate([
            "name"     => 'required|string',
            "email"    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $data['password']      = Hash::make($data['password']);
        $data['platform_role'] = 'super_admin';

        $user = User::create($data);

        $token = Auth::login($user);

        return response()->json([
            'status'        => 'success',
            'message'       => 'User created successfully',
            'user'          => $user,
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ],
        ]);
    }

    public function register(CreateUserRequest $request)
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = $this->service->registration($data);

        $token = Auth::login($user); // JWT generates a unique string
        return response()->json([
            'status'        => 'success',
            'message'       => 'User created successfully',
            'user'          => new UserResource($user),
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ],
        ]);
    }

    public function login(UserLogInRequest $request)
    {

        $credentials = $request->validated();
        $token       = Auth::attempt($credentials); // takes the email and password, checks the database, and verifies the hashed password

        if (! $token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user(); // gets the currently authenticated user model instance from database
        return response()->json([
            'status'        => 'success',
            'user'          => new UserResource($user),
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ],
        ]);

    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status'        => 'success',
            'user'          => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type'  => 'bearer',
            ],
        ]);
    }

}
