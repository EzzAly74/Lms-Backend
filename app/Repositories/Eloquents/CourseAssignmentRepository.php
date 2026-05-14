<?php

namespace App\Repositories\Eloquents;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use App\Models\UserCourseAssignment;
use App\Repositories\Contracts\CourseAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CourseAssignmentRepository extends BaseRepository implements CourseAssignmentRepositoryInterface
{
    public function __construct(CourseAssignment $model)
    {
        parent::__construct($model);
    }

    public function listForCourse(Course $course): Collection
    {
        return $this->model->newQuery()
            ->where('course_id', $course->id)
            ->orderBy('id')
            ->get();
    }

    public function createForCourse(Course $course, array $data): CourseAssignment
    {
        return $this->model->newQuery()->create(
            array_merge($data, ['course_id' => $course->id])
        );
    }

    public function listSubmissions(CourseAssignment $assignment, int $perPage): LengthAwarePaginator
    {
        return UserCourseAssignment::with(['user:id,name,machine_code,department_name', 'assignment'])
            ->where('course_assignment_id', $assignment->id)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function upsertSubmission(CourseAssignment $assignment, User $user, array $data): UserCourseAssignment
    {
        return UserCourseAssignment::updateOrCreate(
            ['user_id' => $user->id, 'course_assignment_id' => $assignment->id],
            $data
        );
    }

    public function findSubmission(int $assignmentId, int $userId): ?UserCourseAssignment
    {
        return UserCourseAssignment::with('assignment')
            ->where('course_assignment_id', $assignmentId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findSubmissionWithRelations(int $id): UserCourseAssignment
    {
        return UserCourseAssignment::with(['user:id,name,machine_code,department_name', 'assignment'])
            ->findOrFail($id);
    }

    public function paginateAllSubmissions(?int $userId, ?int $courseId, int $perPage): LengthAwarePaginator
    {
        return UserCourseAssignment::with(['user:id,name', 'assignment.course:id,title'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($courseId, fn ($q) => $q->whereHas('assignment.course', fn ($inner) => $inner->where('id', $courseId)))
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function deleteSubmissionById(int $id): void
    {
        UserCourseAssignment::whereId($id)->delete();
    }
}
