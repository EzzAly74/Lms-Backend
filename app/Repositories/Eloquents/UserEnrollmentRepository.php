<?php

namespace App\Repositories\Eloquents;

use App\Models\Course;
use App\Models\UsersCourse;
use App\Repositories\Contracts\UserEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserEnrollmentRepository implements UserEnrollmentRepositoryInterface
{
    public function paginateForCourse(Course $course, int $perPage, ?int $groupId): LengthAwarePaginator
    {
        return UsersCourse::with(['user:id,name,machine_code,department_name', 'group'])
            ->where('course_id', $course->id)
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function syncUsers(Course $course, array $userIds, ?int $groupId): void
    {
        $data = [];
        foreach ($userIds as $userId) {
            $data[$userId] = ['group_id' => $groupId];
        }
        $course->users()->syncWithoutDetaching($data);
    }

    public function delete(UsersCourse $enrollment): void
    {
        $enrollment->delete();
    }
}
