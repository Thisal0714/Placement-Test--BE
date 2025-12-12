<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    protected JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Attempt login and return token + user and role_id on success.
     *
     * @return array{token:string,user:User,role_id:?string}
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            throw new \RuntimeException('Invalid credentials');
        }

        $token = $this->jwt->generate(['sub' => $user->id, 'email' => $user->email]);

        return ['token' => $token, 'user' => $user, 'role_id' => $user->role_id];
    }
}
