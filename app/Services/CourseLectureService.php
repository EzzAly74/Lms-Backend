<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLecture;
use App\Repositories\Contracts\CourseLectureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseLectureService
{
    public function __construct(
        private readonly CourseLectureRepositoryInterface $repo
    ) {}

    public function listForCourse(Course $course): Collection
    {
        return $this->repo->sectionsWithLecturesForCourse($course);
    }

    public function create(Course $course, array $data): CourseLecture
    {
        return $this->repo->createForCourse($course, $data);
    }

    public function update(CourseLecture $lecture, array $data): CourseLecture
    {
        /** @var CourseLecture */
        return $this->repo->update($lecture, $data);
    }

    public function delete(CourseLecture $lecture): void
    {
        $this->repo->delete($lecture);
    }
}
