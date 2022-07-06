<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Role $role */
        $role = $this->resource;
        return [
            'id' => $role->getAttribute('id'),
            'name' => $role->getAttribute('name'),
            'permissions' => PermissionResource::collection($role->permissions),
        ];
    }
}

