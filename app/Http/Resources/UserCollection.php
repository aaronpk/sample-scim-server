<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
            'Resources' => $this->collection,
            'totalResults' => count($this->collection),
            'itemsPerPage' => 1,
            'startIndex' => 1,
        ];
    }
}
