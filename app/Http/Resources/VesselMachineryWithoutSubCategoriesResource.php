<?php

namespace App\Http\Resources;

use App\Models\VesselMachinery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VesselMachineryWithoutSubCategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var VesselMachinery $vesselMachinery */
        $vesselMachinery = $this->resource;
        return [
            'id' => $vesselMachinery->getAttribute('id'),
            'vessel' => new VesselResource($vesselMachinery->vessel),
            'machinery' => new MachineryWithoutSubCategoriesResource($vesselMachinery->machinery),
            'incharge_rank' => new RankResource($vesselMachinery->inchargeRank),
            'model' => $vesselMachinery->model ?: new MachineryModelResource($vesselMachinery->model),
            'maker' => $vesselMachinery->maker ?: new MachineryMakerResource($vesselMachinery->maker),
        ];
    }
}
