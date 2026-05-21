<?php

namespace App\Repositories\Eloquents;

use App\Models\Course;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStatistics(): array
    {
        $hasLearnerType = Schema::hasColumn('users', 'learner_type');

        $onlineLearnersSql = $hasLearnerType
            ? "(SELECT COUNT(*) FROM users WHERE learner_type = 'online')"
            : '(SELECT COUNT(*) FROM users)';

        $offlineLearnersSql = $hasLearnerType
            ? "(SELECT COUNT(*) FROM users WHERE learner_type = 'offline')"
            : '0';

        return (array) DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM courses WHERE active = 1)                          AS active_courses,
                (SELECT COUNT(*) FROM courses WHERE active = 0)                          AS awaiting_publish,
                (SELECT COUNT(*) FROM courses)                                            AS courses,
                (SELECT COUNT(*) FROM users)                                              AS users,
                (SELECT COUNT(*) FROM users)                                              AS active_learners,
                {$onlineLearnersSql}                                                      AS active_learners_online,
                {$offlineLearnersSql}                                                     AS active_learners_offline,
                (SELECT COUNT(*) FROM instructors)                                        AS instructors,
                (SELECT COUNT(*) FROM articles WHERE active = 1)                         AS articles,
                (SELECT COUNT(*) FROM course_ratings)                                    AS ratings,
                (SELECT COUNT(*) FROM course_lecture_questions)                          AS lecture_questions,
                (SELECT COUNT(*) FROM course_lecture_questions WHERE answer IS NULL)     AS unanswered_questions,
                (SELECT COUNT(*) FROM user_course_assignments)                           AS user_assignments
        ");
    }

    public function getTopCourses(int $limit): Collection
    {
        return Course::query()
            ->select('id', 'title', 'active')
            ->with(['instructors:id,name'])
            ->selectRaw('
                (SELECT COUNT(*) FROM users_courses uc WHERE uc.course_id = courses.id)                                        AS users_count,
                (SELECT COUNT(*) FROM users_courses uc WHERE uc.course_id = courses.id AND uc.updated_at > uc.created_at)      AS completed_count
            ')
            ->orderByDesc('users_count')
            ->limit($limit)
            ->get();
    }

    public function getEnrollmentTrend(int $days = 30): array
    {
        $rows = DB::select("
            SELECT
                d.gen_date AS date,
                COALESCE(e.enrollments, 0) AS enrollments,
                COALESCE(c.completions, 0) AS completions
            FROM (
                SELECT DATE_SUB(CURDATE(), INTERVAL n DAY) AS gen_date
                FROM (
                    SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
                    UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
                    UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
                    UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
                    UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
                ) nums
                WHERE n < ?
            ) d
            LEFT JOIN (
                SELECT DATE(created_at) AS dt, COUNT(*) AS enrollments
                FROM users_courses
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
            ) e ON e.dt = d.gen_date
            LEFT JOIN (
                SELECT DATE(updated_at) AS dt, COUNT(*) AS completions
                FROM users_courses
                WHERE updated_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  AND updated_at > created_at
                GROUP BY DATE(updated_at)
            ) c ON c.dt = d.gen_date
            ORDER BY d.gen_date ASC
        ", [$days, $days, $days]);

        return array_map(static fn ($r) => [
            'date'        => $r->date,
            'enrollments' => (int) $r->enrollments,
            'completions' => (int) $r->completions,
        ], $rows);
    }

    public function getRecentNotifications(int $limit): array
    {
        $stats = $this->getStatistics();
        $items = [];

        if ((int) ($stats['unanswered_questions'] ?? 0) > 0) {
            $items[] = [
                'title'  => 'Unanswered lecture questions',
                'detail' => (int) $stats['unanswered_questions'] . ' questions need a response',
                'time'   => 'now',
                'type'   => 'warning',
            ];
        }

        if ((int) ($stats['awaiting_publish'] ?? 0) > 0) {
            $items[] = [
                'title'  => 'Courses awaiting publish',
                'detail' => (int) $stats['awaiting_publish'] . ' inactive courses',
                'time'   => 'today',
                'type'   => 'info',
            ];
        }

        if ((int) ($stats['user_assignments'] ?? 0) > 0) {
            $items[] = [
                'title'  => 'Assignment submissions',
                'detail' => (int) $stats['user_assignments'] . ' total submissions',
                'time'   => 'today',
                'type'   => 'info',
            ];
        }

        return array_slice($items, 0, $limit);
    }
}
