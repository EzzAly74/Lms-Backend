<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'review'     => $this->review,
            'user'       => $this->whenLoaded('user', fn () => [
                'id'           => $this->user->id,
                'name'         => $this->user->name,
                'machine_code' => $this->user->machine_code,
            ]),
            'course_id'  => $this->course_id,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
