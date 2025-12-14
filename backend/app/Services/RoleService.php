<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleService
{
    /**
     * Return all roles.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function list()
    {
        return Role::all();
    }

    /**
     * Get a role by id or throw ModelNotFoundException.
     */
    public function get(string $id): Role
    {
        return Role::findOrFail($id);
    }

    /**
     * Create a new role after validating input.
     *
     * @param array $data
     * @return Role
     * @throws ValidationException
     * @throws QueryException
     */
    public function create(array $data): Role
    {
        $validator = Validator::make($data, [
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Role::create($validator->validated());
    }

    /**
     * Update an existing role.
     *
     * @param string $id
     * @param array $data
     * @return Role
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws QueryException
     */
    public function update(string $id, array $data): Role
    {
        $role = $this->get($id);

        $validator = Validator::make($data, [
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $id . ',id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $role->update($validator->validated());

        return $role;
    }

    /**
     * Delete a role by id.
     *
     * @param string $id
     * @return void
     * @throws ModelNotFoundException
     */
    public function delete(string $id): void
    {
        $role = $this->get($id);
        $role->delete();
    }
}
