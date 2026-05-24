<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'             => $this->id,
            'course_id'      => $this->course_id,
            // Localized display name for tables, plus the raw translations
            // map so the edit dialog can pre-fill both EN/AR inputs without
            // a follow-up request.
            'name'           => $this->getTranslation('name', $locale),
            'name_translations' => [
                'en' => $this->getTranslation('name', 'en'),
                'ar' => $this->getTranslation('name', 'ar'),
            ],
            // Cohort metadata (Figma 332:9988, 332:10708). All nullable.
            'start_date'     => $this->start_date?->format('Y-m-d'),
            'end_date'       => $this->end_date?->format('Y-m-d'),
            'capacity'       => $this->capacity !== null ? (int) $this->capacity : null,
            'status'         => $this->status ?? 'scheduled',
            // Counted inline by the repository (`withCount`). Falls back
            // to a fresh sub-query if a caller hand-builds a section model.
            'enrolled_count' => (int) ($this->enrolled_count
                ?? $this->enrollments()->count()),

            'lectures' => $this->whenLoaded('lectures', fn () => CourseLectureResource::collection($this->lectures)),
            'exams'    => $this->whenLoaded('exams', fn () => CourseExamResource::collection($this->exams)),
        ];
    }
}
