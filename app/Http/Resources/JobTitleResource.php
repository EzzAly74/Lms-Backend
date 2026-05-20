<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobTitleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'qualifications_count' => $this->whenCounted('qualificationSkills'),
            'qualifications'       => $this->whenLoaded(
                'qualificationSkills',
                fn () => $this->qualificationSkills->map(fn ($skill) => [
                    'id'   => $skill->id,
                    'name' => $skill->getTranslation('name', app()->getLocale()),
                ]),
            ),
        ];
    }
}
