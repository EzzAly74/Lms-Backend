<?php

namespace App\Repositories\Eloquents;

use App\Models\Course;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStatistics(): array
    {
        return (array) DB::selectOne('
            SELECT
                (SELECT COUNT(*) FROM courses WHERE active = 1)                          AS courses,
                (SELECT COUNT(*) FROM users)                                              AS users,
                (SELECT COUNT(*) FROM instructors)                                        AS instructors,
                (SELECT COUNT(*) FROM articles WHERE active = 1)                         AS articles,
                (SELECT COUNT(*) FROM course_ratings)                                    AS ratings,
                (SELECT COUNT(*) FROM course_lecture_questions)                          AS lecture_questions,
                (SELECT COUNT(*) FROM course_lecture_questions WHERE answer IS NULL)     AS unanswered_questions,
                (SELECT COUNT(*) FROM user_course_assignments)                           AS user_assignments
        ');
    }

    public function getTopCourses(int $limit): Collection
    {
        return Course::active()
            ->select('id', 'title')
            ->selectRaw('(SELECT COUNT(*) FROM users_courses WHERE users_courses.course_id = courses.id) AS users_count')
            ->orderByDesc('users_count')
            ->limit($limit)
            ->get();
    }
}
