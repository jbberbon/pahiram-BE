<?php

namespace App\Http\Resources\ManagePenalty\LendingOffice;

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
        return [
            'transactions' => $this->collection->toArray(), // The collection of endorsements
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
}
