<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['login', 'register']
        ]);
    }

    public function register(CreateUserRequest $request)
    {
        $userData = $request->only(['name', 'email']);
        $userData['password'] = Hash::make($request->get('password'));

        $user = User::create($userData);

        $role = Permission::where('name','CUSTOMER')->first();
        $user->permissions()->attach($role);

        return response()->json([
            'message' => 'Usuário cadastrado!',
            'user' => $user,
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token =  auth()->attempt($credentials);
        if (!$token) {
            return response()->json([
                'error' => 'Não autorizado'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'token' => $token,
            'type' => 'Bearer'
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Deslogado com sucesso!'
        ], Response::HTTP_OK);
    }
}
