<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SignupService
{
    protected JwtService $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Register a new user and return the created User.
     *
     * @param array $data ['name','email','password']
     * @return User
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }
}
