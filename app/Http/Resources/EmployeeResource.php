<?php

namespace App\Http\Resources;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Employee $employee */
        $employee = $this->resource;
        /** @var User $user */
        $user = $employee->user;

        $rolePermissions = collect([]);
        foreach ($user->roles as $role) {
            $rolePermissions = $rolePermissions->merge($role->permissions);
        }

        $permissions = collect([]);
        if ($user->permissions->count()) {
            $permissions = $user->permissions;
        } else {
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
        }

        return [
            'id' => $employee->getAttribute('id'),
            'first_name' => $user->getAttribute('first_name'),
            'middle_name' => $user->getAttribute('middle_name'),
            'last_name' => $user->getAttribute('last_name'),
            'full_name' => $user->getAttribute('full_name'),
            'email' => $user->getAttribute('email'),
            'status' => new UserStatusResource($user->status),
            'department' => new EmployeeDepartmentResource($employee->department),
            'id_number' => $employee->getAttribute('id_number'),
            'position' => $employee->getAttribute('position'),
            'is_admin' => $user->hasRole(config('user.roles.admin')),
            'role_permissions' => (object)$this->parseToPermissionsArray($rolePermissions),
            'permissions' => (object)$this->parseToPermissionsArray($permissions),
        ];
    }


    public function parseToPermissionsArray(Collection $permissions): array
    {
        $parsedPermissions = [];

        foreach ($permissions as $permission) {
            $parsedPermissions[$permission->name] = $permission->id;
        }

        return $parsedPermissions;
    }
}
