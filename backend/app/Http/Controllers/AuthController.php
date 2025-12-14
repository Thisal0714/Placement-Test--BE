<?php

namespace App\Http\Controllers;

use App\Services\LoginService;
use App\Services\JwtService;
use App\Services\SignupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\User;

class AuthController extends Controller
{
    protected LoginService $loginService;
    protected JwtService $jwt;
    protected SignupService $signupService;

    public function __construct(LoginService $loginService, JwtService $jwt, SignupService $signupService)
    {
        $this->loginService = $loginService;
        $this->jwt = $jwt;
        $this->signupService = $signupService;
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            // Require first and last name explicitly (no `name` field used).
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|uuid',
        ]);
        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'message' => 'Email already registered',
                'errors' => ['email' => ['The email address is already in use.']],
            ], 409);
        }

            try {
                if (!array_key_exists('role_id', $data)) {
                    $data['role_id'] = null;
                }

                $user = $this->signupService->register($data);
            } catch (QueryException $e) {
                $sqlState = $e->errorInfo[0] ?? null;
                if ($sqlState === '23505') {
                    return response()->json([
                        'message' => 'Email already registered',
                        'errors' => ['email' => ['The email address is already in use.']],
                    ], 409);
                }

                return response()->json(['message' => 'Could not create user'], 500);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Could not create user'], 500);
            }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id','first_name','last_name','email','role_id']),
        ], 200);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $res = $this->loginService->login($data['email'], $data['password']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'User signed in successfully',
            'token' => $res['token'],
            'user' => $res['user']->only(['id','first_name','last_name','email','role_id']),
        ]);
    }

    public function logout(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json(['message' => 'Authorization token required'], 400);
        }

        $token = trim(substr($auth, 7));
        try {
            $payload = $this->jwt->decode($token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $jti = $payload['jti'] ?? null;
        $exp = $payload['exp'] ?? null;
        if (!$jti || !$exp) {
            return response()->json(['message' => 'Invalid token payload'], 400);
        }

        DB::table('jwt_revocations')->insert([
            'jti' => $jti,
            'expires_at' => date('Y-m-d H:i:s', $exp),
        ]);

        return response()->json(['message' => 'Logged out']);
    }
}
