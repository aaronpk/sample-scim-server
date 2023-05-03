<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
            'id' => $this->id,
            'externalId' => $this->external_id,
            'userName' => $this->username,
            'name' => [
                'formatted' => $this->first_name.' '.$this->last_name,
                'givenName' => $this->first_name,
                'familyName' => $this->last_name,
            ],
            'emails' => [
                ['value' => $this->email]
            ],
            'active' => (bool)$this->active,
        ];
    }
}
