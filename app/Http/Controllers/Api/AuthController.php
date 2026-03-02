<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UserLogInRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(CreateUserRequest $request)
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user             = User::create($data);
        $token            = Auth::login($user); // JWT generates a unique string
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
