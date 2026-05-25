<?php

declare(strict_types=1);

namespace App\Repositories\Eloquents\Mobile;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use App\Repositories\Contracts\Mobile\AcademyRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent implementation of the Academy read surface (S-01 → S-04).
 *
 * Concept: a course is "available for the user" iff it has at least
 * one cohort (course_sections row) that is:
 *
 *   1.  `stored status NOT 'inactive'`        — admin override veto
 *   2.  `start_date >= today`                  — future cohort
 *   3.  `effective enrolment_closes_at >= today` where the effective
 *        deadline is the persisted `enrolment_closes_at` if present,
 *        otherwise `start_date - mobile.academy.default_close_offset_days`.
 *   4.  `enrolled_count < capacity`            — seats remaining
 *   5.  user is not already enrolled in *any* cohort of the course
 *
 * Everything below is expressed as raw SQL inside `whereExists`
 * subqueries so the filter pushes down to the database and a single
 * paginated query produces the list without N+1.
 */
final class AcademyRepository implements AcademyRepositoryInterface
{
    public function __construct(
        private readonly Course $course,
        private readonly Category $category,
        private readonly CourseSection $section,
    ) {}

    public function countAvailableForUser(User $user, Carbon $now, int $defaultCloseOffsetDays): int
    {
        return $this->baseAvailableQuery($user, $now, $defaultCloseOffsetDays)->count();
    }

    public function categoriesWithAvailableCount(User $user, Carbon $now, int $defaultCloseOffsetDays): EloquentCollection
    {
        // We want every category that owns at least one available
        // course. We count subquery rows per category so the count
        // matches what `paginateAvailable` would yield for that filter.
        $today  = $now->toDateString();
        $offset = max(0, $defaultCloseOffsetDays);

        return $this->category->newQuery()
            ->select(['categories.id', 'categories.name'])
            ->selectSub(
                $this->buildAvailableCountSubquery($user, $today, $offset),
                'available_count',
            )
            ->whereExists(function ($q) use ($user, $today, $offset) {
                $q->from('courses')
                  ->whereColumn('courses.category_id', 'categories.id')
                  ->where(function ($q2) use ($user, $today, $offset) {
                      $this->applyAvailableExists($q2, $user, $today, $offset);
                  });
            })
            ->orderBy('categories.id')
            ->get();
    }

    public function paginateAvailable(
        User    $user,
        Carbon  $now,
        int     $defaultCloseOffsetDays,
        int     $perPage,
        ?int    $categoryId,
        ?string $search,
    ): LengthAwarePaginator {
        $locale = app()->getLocale();

        return $this->baseAvailableQuery($user, $now, $defaultCloseOffsetDays)
            ->select([
                'courses.id',
                'courses.title',
                'courses.description',
                'courses.course_type',
                'courses.category_id',
                'courses.image',
                'courses.hours',
                'courses.certificate',
                'courses.created_at',
            ])
            ->with([
                'category:id,name',
                'qualificationSkills:id,name',
                'instructors:id,name,image',
                // Eager-load only the sections we need to surface the
                // next cohort. The repository is responsible for
                // pre-trimming so the resource doesn't have to.
                'sections' => fn ($q) => $q
                    ->orderBy('start_date')
                    ->select(['id', 'course_id', 'name', 'start_date', 'end_date', 'capacity', 'enrolment_closes_at', 'status']),
            ])
            ->withCount(['ratings as rating_count'])
            ->withAvg('ratings as rating_avg', 'rating')
            ->when($categoryId, fn ($q) => $q->where('courses.category_id', $categoryId))
            ->when($search, fn ($q) => $q->where(function (Builder $inner) use ($search, $locale) {
                $inner->where("courses.title->{$locale}", 'LIKE', "%{$search}%")
                      ->orWhere('courses.title->en', 'LIKE', "%{$search}%")
                      ->orWhere('courses.title->ar', 'LIKE', "%{$search}%");
            }))
            ->orderByDesc('courses.id')
            ->paginate($perPage);
    }

    public function findForDetail(int $courseId): Course
    {
        return $this->course->newQuery()
            ->with([
                'category:id,name',
                'instructors:id,name,image',
                'qualificationSkills:id,name',
                'sections' => fn ($q) => $q
                    ->orderBy('start_date')
                    ->withCount(['enrollments as enrolled_count']),
                'sections.sessions' => fn ($q) => $q
                    ->orderBy('session_date')
                    ->orderBy('time_from'),
                // Lectures drive the S-03 "Course Content" block.
                'lectures' => fn ($q) => $q->orderBy('id'),
            ])
            ->withCount([
                'users as users_count',
                'ratings as rating_count',
            ])
            ->withAvg('ratings as rating_avg', 'rating')
            ->findOrFail($courseId);
    }

    public function nextJoinableCohort(Course $course, User $user, Carbon $now, int $defaultCloseOffsetDays): ?CourseSection
    {
        $today  = $now->toDateString();
        $offset = max(0, $defaultCloseOffsetDays);

        return $this->section->newQuery()
            ->withCount(['enrollments as enrolled_count'])
            ->where('course_id', $course->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'inactive');
            })
            ->whereNotNull('start_date')
            ->whereDate('start_date', '>=', $today)
            ->where(function ($q) use ($today, $offset) {
                // Effective deadline = enrolment_closes_at OR start_date - offset.
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('enrolment_closes_at')
                       ->whereDate('enrolment_closes_at', '>=', $today);
                })->orWhere(function ($q2) use ($today, $offset) {
                    $q2->whereNull('enrolment_closes_at')
                       ->whereRaw('DATE_SUB(start_date, INTERVAL ? DAY) >= ?', [$offset, $today]);
                });
            })
            // Capacity check: capacity NULL => unlimited; otherwise
            // enrolled_count must be < capacity.
            ->where(function ($q) {
                $q->whereNull('capacity')
                  ->orWhereRaw('(SELECT COUNT(*) FROM users_courses uc WHERE uc.group_id = course_sections.id) < course_sections.capacity');
            })
            // Skip cohorts the user is already in.
            ->whereNotExists(function ($q) use ($user) {
                $q->from('users_courses')
                  ->whereColumn('users_courses.group_id', 'course_sections.id')
                  ->where('users_courses.user_id', $user->id);
            })
            ->orderBy('start_date')
            ->orderBy('id')
            ->first();
    }

    public function isEnrolledInCourse(User $user, int $courseId): bool
    {
        return DB::table('users_courses')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();
    }

    public function isEnrolledInCohort(User $user, int $cohortId): bool
    {
        return DB::table('users_courses')
            ->where('user_id', $user->id)
            ->where('group_id', $cohortId)
            ->exists();
    }

    // ────────────────────────────────────────────────────────────
    // Internals
    // ────────────────────────────────────────────────────────────

    private function baseAvailableQuery(User $user, Carbon $now, int $defaultCloseOffsetDays): Builder
    {
        $today  = $now->toDateString();
        $offset = max(0, $defaultCloseOffsetDays);

        return $this->course->newQuery()
            ->where(function ($q) use ($user, $today, $offset) {
                $this->applyAvailableExists($q, $user, $today, $offset);
            });
    }

    /**
     * Add the "joinable cohort exists for this user" predicate.
     * Extracted so countAvailableForUser, paginateAvailable, and
     * categoriesWithAvailableCount stay in lock-step.
     *
     * The `$q` builder may be either an Eloquent Builder (when invoked
     * from `baseAvailableQuery`, where the outer query is a model
     * query) or a Query Builder (when invoked from inside a
     * `whereExists` closure, where Laravel passes the raw query
     * builder). Every method we call below exists on both.
     *
     * @param  Builder|QueryBuilder  $q
     * @return Builder|QueryBuilder
     */
    private function applyAvailableExists(Builder|QueryBuilder $q, User $user, string $today, int $offset): Builder|QueryBuilder
    {
        return $q->whereExists(function ($sub) use ($user, $today, $offset) {
            $sub->from('course_sections')
                ->whereColumn('course_sections.course_id', 'courses.id')
                ->where(function ($q2) {
                    $q2->whereNull('course_sections.status')
                       ->orWhere('course_sections.status', '!=', 'inactive');
                })
                ->whereNotNull('course_sections.start_date')
                ->whereDate('course_sections.start_date', '>=', $today)
                ->where(function ($q2) use ($today, $offset) {
                    $q2->where(function ($q3) use ($today) {
                        $q3->whereNotNull('course_sections.enrolment_closes_at')
                           ->whereDate('course_sections.enrolment_closes_at', '>=', $today);
                    })->orWhere(function ($q3) use ($today, $offset) {
                        $q3->whereNull('course_sections.enrolment_closes_at')
                           ->whereRaw(
                               'DATE_SUB(course_sections.start_date, INTERVAL ? DAY) >= ?',
                               [$offset, $today],
                           );
                    });
                })
                ->where(function ($q2) {
                    $q2->whereNull('course_sections.capacity')
                       ->orWhereRaw(
                           '(SELECT COUNT(*) FROM users_courses uc WHERE uc.group_id = course_sections.id) < course_sections.capacity',
                       );
                })
                ->whereNotExists(function ($q2) use ($user) {
                    $q2->from('users_courses')
                       ->whereColumn('users_courses.group_id', 'course_sections.id')
                       ->where('users_courses.user_id', $user->id);
                });
        });
    }

    /**
     * Build a "courses available for this user under this category"
     * count subquery — used by the chip badges on S-02.
     *
     * @return \Closure(\Illuminate\Database\Query\Builder): \Illuminate\Database\Query\Builder
     */
    private function buildAvailableCountSubquery(User $user, string $today, int $offset): \Closure
    {
        return function ($q) use ($user, $today, $offset) {
            $q->selectRaw('COUNT(*)')
              ->from('courses')
              ->whereColumn('courses.category_id', 'categories.id')
              ->whereExists(function ($sub) use ($user, $today, $offset) {
                  $sub->from('course_sections')
                      ->whereColumn('course_sections.course_id', 'courses.id')
                      ->where(function ($q2) {
                          $q2->whereNull('course_sections.status')
                             ->orWhere('course_sections.status', '!=', 'inactive');
                      })
                      ->whereNotNull('course_sections.start_date')
                      ->whereDate('course_sections.start_date', '>=', $today)
                      ->where(function ($q2) use ($today, $offset) {
                          $q2->where(function ($q3) use ($today) {
                              $q3->whereNotNull('course_sections.enrolment_closes_at')
                                 ->whereDate('course_sections.enrolment_closes_at', '>=', $today);
                          })->orWhere(function ($q3) use ($today, $offset) {
                              $q3->whereNull('course_sections.enrolment_closes_at')
                                 ->whereRaw(
                                     'DATE_SUB(course_sections.start_date, INTERVAL ? DAY) >= ?',
                                     [$offset, $today],
                                 );
                          });
                      })
                      ->where(function ($q2) {
                          $q2->whereNull('course_sections.capacity')
                             ->orWhereRaw(
                                 '(SELECT COUNT(*) FROM users_courses uc WHERE uc.group_id = course_sections.id) < course_sections.capacity',
                             );
                      })
                      ->whereNotExists(function ($q2) use ($user) {
                          $q2->from('users_courses')
                             ->whereColumn('users_courses.group_id', 'course_sections.id')
                             ->where('users_courses.user_id', $user->id);
                      });
              });
        };
    }
}
