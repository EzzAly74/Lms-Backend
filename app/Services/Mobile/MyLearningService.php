<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Course;
use App\Models\User;
use App\Repositories\Contracts\Mobile\MyLearningRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Compose the S-05 view-models. Pure orchestration over the repo +
 * MobileSettings — never queries the DB directly.
 */
final class MyLearningService
{
    public function __construct(
        private readonly MyLearningRepositoryInterface $repository,
        private readonly MobileSettings $settings,
    ) {}

    public function activeCoursesPaginated(User $user): LengthAwarePaginator
    {
        return $this->repository->activeCoursesForUser(
            $user,
            $this->settings->myLearningActivePerPage(),
        );
    }

    public function activeCoursesPreview(User $user): EloquentCollection
    {
        return $this->repository->previewActiveCourses(
            $user,
            $this->settings->myLearningActivePreviewCount(),
        );
    }

    public function progressFor(User $user, Course $course, int $cohortId, string $locale): array
    {
        return $this->repository->courseProgressSummary(
            $user,
            $course->id,
            $cohortId,
            $locale,
        );
    }

    public function sessionsAttendance(User $user, int $courseId, int $cohortId): Collection
    {
        return $this->repository->sessionsAttendance($user, $courseId, $cohortId);
    }

    public function userRatingForCourse(User $user, int $courseId): ?object
    {
        return $this->repository->userRatingForCourse($user, $courseId);
    }

    /**
     * Live-session detection driver for the S-05 card. Returns null
     * if no session is happening now, otherwise the smallest
     * relevant session row.
     *
     * The detection re-uses the same buffers as the passcode flow so
     * "Live Now" and "Mark Present" stay in lock-step.
     */
    public function liveSessionFor(User $user, int $courseId, int $cohortId, Carbon $now): ?object
    {
        $openBuf  = $this->settings->attendanceSessionOpenBufferMinutes();
        $graceBuf = $this->settings->attendanceSessionGraceMinutes();

        return \DB::table('course_sessions')
            ->where('course_id', $courseId)
            ->where('section_id', $cohortId)
            ->where(function ($q) use ($now) {
                $q->whereNull('session_date')
                  ->orWhereDate('session_date', $now->toDateString());
            })
            ->where(function ($q) use ($now, $openBuf, $graceBuf) {
                $q->where(function ($q2) {
                    $q2->whereNull('time_from')->whereNull('time_to');
                })->orWhere(function ($q2) use ($now, $openBuf, $graceBuf) {
                    $q2->whereRaw(
                        'TIMESTAMPDIFF(MINUTE, ?, time_from) <= ?',
                        [$now->format('H:i:s'), $openBuf],
                    )->whereRaw(
                        'TIMESTAMPDIFF(MINUTE, time_to, ?) <= ?',
                        [$now->format('H:i:s'), $graceBuf],
                    );
                });
            })
            ->orderBy('time_from')
            ->orderBy('id')
            ->first();
    }
}
