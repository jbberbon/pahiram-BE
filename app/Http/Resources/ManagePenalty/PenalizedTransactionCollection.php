<?php

namespace App\Http\Resources\ManagePenalty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PenalizedTransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        // return BorrowRequestResource::collection($this->collection);
        return $this->collection->map->toArray($request)->all();
    }
}
