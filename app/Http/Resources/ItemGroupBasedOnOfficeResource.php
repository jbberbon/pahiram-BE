<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemGroupBasedOnOfficeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model_name' => $this->model_name,
        ];
    }
}
