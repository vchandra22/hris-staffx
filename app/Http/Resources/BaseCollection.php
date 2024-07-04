<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    private $paginationData = [];
    public function __construct($resource, $paginationData = null)
    {
        $this->paginationData = $paginationData;

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'list' => $this->collection,
            'meta' => [
                'links' => $this->paginationData->getUrlRange(1, $this->paginationData->lastPage()),
                'total' => $this->paginationData->total(),
            ]
        ];
    }
}
