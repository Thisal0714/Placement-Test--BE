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
        * @param array $data ['first_name','last_name','email','password','role_id']
     * @return User
     */
    public function register(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        return $user;
    }
}
