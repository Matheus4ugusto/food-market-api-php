<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
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

        return response()->json([
            'message' => 'Usuário cadastrado!',
            'user' => $user,
        ], HttpResponse::HTTP_CREATED);
    }
}
