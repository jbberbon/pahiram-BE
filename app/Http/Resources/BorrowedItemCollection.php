<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BorrowedItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        // return BorrowRequestResource::collection($this->collection);
        // return $this->collection->map->toArray($request)->all();
        // return $this->collection->map->toArray($request)->all();
        // $collection =  new BorrowedItemResource($this->collection);
        // return $collection->map->toArray();

        return BorrowedItemResource::collection($this->collection)->toArray($request);
    }
}
