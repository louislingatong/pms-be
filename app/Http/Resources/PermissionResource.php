<?php

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Permission $permission */
        $permission = $this->resource;
        return [
            'id' => $permission->getAttribute('id'),
            'name' => $permission->getAttribute('name'),
        ];
    }
}
