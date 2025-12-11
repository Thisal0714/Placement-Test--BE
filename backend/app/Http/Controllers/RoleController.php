<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json(['roles' => $roles]);
    }

    public function show($id)
    {
        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return response()->json(['role' => $role]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        try {
            $role = Role::create($data);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23505') {
                return response()->json([
                    'message' => 'Role already exists',
                    'errors' => ['role_name' => ['The role name is already in use.']],
                ], 409);
            }
            return response()->json(['message' => 'Could not create role'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not create role'], 500);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $data = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $id . ',id',
        ]);

        try {
            $role->update($data);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23505') {
                return response()->json([
                    'message' => 'Role name already in use',
                    'errors' => ['role_name' => ['The role name is already in use.']],
                ], 409);
            }
            return response()->json(['message' => 'Could not update role'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not update role'], 500);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role,
        ]);
    }

    public function delete($id)
    {
        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        try {
            $role->delete();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not delete role'], 500);
        }

        return response()->json(['message' => 'Role deleted'], 204);
    }
}
