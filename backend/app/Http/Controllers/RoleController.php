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
        return $this->successResponse(['roles' => $roles]);
    }

    public function show($id)
    {
        try {
            $role = $this->roleService->get($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Role not found', null, 404);
        }

        return $this->successResponse(['role' => $role]);
    }

    public function create(Request $request)
    {
        try {
            $role = $this->roleService->create($request->all());
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23505') {
                return $this->errorResponse('Role already exists', ['role_name' => ['The role name is already in use.']], 409);
            }
            return $this->errorResponse('Could not create role', null, 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not create role', null, 500);
        }

        return $this->successResponse(['role' => $role], 'Role created successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $role = $this->roleService->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Role not found', null, 404);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            if ($sqlState === '23505') {
                return $this->errorResponse('Role name already in use', ['role_name' => ['The role name is already in use.']], 409);
            }
            return $this->errorResponse('Could not update role', null, 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not update role', null, 500);
        }

        return $this->successResponse(['role' => $role], 'Role updated successfully');
    }

    public function delete($id)
    {
        try {
            $this->roleService->delete($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Role not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not delete role', null, 500);
        }

        return $this->successResponse(null, 'Role deleted');
    }
}
