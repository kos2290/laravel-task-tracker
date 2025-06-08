<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'status'             => $this->status,
            'assigned_user_id'   => $this->assigned_user_id,
            'created_by_user_id' => $this->created_by_user_id,
            'assigned_user'      => new UserResource($this->whenLoaded('assignedUser')),
            'created_by_user'    => new UserResource($this->whenLoaded('createdByUser')),
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
