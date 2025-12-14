<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function userList()
    {
        $users = User::with('role')->get();
        return $this->successResponse(['users' => $users]);
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return $this->successResponse(['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|nullable|uuid',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $this->successResponse(['user' => $user->load('role')], 'User updated successfully');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}