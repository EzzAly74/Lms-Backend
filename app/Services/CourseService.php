<?php

namespace App\Services;

use App\Http\Traits\HasFile;
use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CourseService
{
    use HasFile;

    public function __construct(
        private readonly CourseRepositoryInterface $courseRepository,
    ) {}

    public function list(
        int     $perPage    = 15,
        ?string $search     = null,
        ?int    $categoryId = null,
        ?bool   $active     = null,
        ?string $courseType = null,
    ): LengthAwarePaginator {
        return $this->courseRepository->paginateWithFilters(
            $perPage, $search, $categoryId, $active, $courseType,
        );
    }

    public function allActive(): Collection
    {
        return $this->courseRepository->allActive();
    }

    public function activePluckedTitles(): Collection
    {
        return $this->courseRepository->activePluckedTitles();
    }

    public function findOrFail(int $id): Course
    {
        return $this->courseRepository->findWithRelations($id);
    }

    public function create(array $data, ?UploadedFile $image = null): Course
    {
        if ($image) {
            $data['image'] = $this->uploadRequestFile('Course', request(), 'image');
        }
        $data['active']            = (bool) ($data['active']            ?? false);
        $data['outside_materials'] = (bool) ($data['outside_materials'] ?? false);
        $data['is_evaluate']       = (bool) ($data['is_evaluate']       ?? false);
        $data['allow_attendances'] = (bool) ($data['allow_attendances'] ?? false);

        $instructors           = $data['instructors']             ?? [];
        $qualificationSkillIds = $data['qualification_skill_ids'] ?? [];
        unset($data['instructors'], $data['qualification_skill_ids']);

        $course = $this->courseRepository->create($data);

        if ($instructors) {
            $course->instructors()->attach($instructors);
        }

        if ($qualificationSkillIds) {
            $course->qualificationSkills()->sync(array_values(array_unique($qualificationSkillIds)));
        }

        return $this->courseRepository->findWithBasicRelations($course->id);
    }

    public function update(Course $course, array $data, ?UploadedFile $image = null): Course
    {
        if ($image) {
            $data['image'] = $this->uploadRequestFile('Course', request(), 'image');
        }
        $data['active']            = (bool) ($data['active']            ?? false);
        $data['outside_materials'] = (bool) ($data['outside_materials'] ?? false);
        $data['is_evaluate']       = (bool) ($data['is_evaluate']       ?? false);
        $data['allow_attendances'] = (bool) ($data['allow_attendances'] ?? false);

        $instructors           = $data['instructors']             ?? null;
        $hasSkillsPayload      = array_key_exists('qualification_skill_ids', $data);
        $qualificationSkillIds = $data['qualification_skill_ids'] ?? null;
        unset($data['instructors'], $data['qualification_skill_ids']);

        $course = $this->courseRepository->update($course, $data);

        if (!is_null($instructors)) {
            $course->instructors()->sync($instructors);
        }

        if ($hasSkillsPayload) {
            $course->qualificationSkills()->sync(
                array_values(array_unique((array) ($qualificationSkillIds ?? []))),
            );
        }

        return $this->courseRepository->findWithBasicRelations($course->id);
    }

    public function delete(Course $course): bool
    {
        return $this->courseRepository->delete($course);
    }
}
