<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->list();
        return response()->json(['roles' => $roles]);
    }

    public function show($id)
    {
        try {
            $role = $this->roleService->get($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json(['role' => $role]);
    }

    public function create(Request $request)
    {
        try {
            $role = $this->roleService->create($request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $role = $this->roleService->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Role not found'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
        try {
            $this->roleService->delete($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Role not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not delete role'], 500);
        }

        return response()->json(['message' => 'Role deleted'], 200);
    }
}
