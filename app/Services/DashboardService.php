<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repo
    ) {}

    public function getSummary(): array
    {
        $statistics      = $this->repo->getStatistics();
        $enrollmentTrend = $this->repo->getEnrollmentTrend(30);
        $locale     = app()->getLocale();
        $topCourses = $this->repo->getTopCourses(10)
            ->map(function ($c) use ($locale) {
                $enrolled  = (int) ($c->users_count ?? 0);
                $completed = (int) ($c->completed_count ?? 0);
                $percent   = $enrolled > 0 ? (int) round($completed * 100 / $enrolled) : 0;

                $instructorName = optional($c->instructors->first())
                    ?->getTranslation('name', $locale);

                return [
                    'id'                 => $c->id,
                    'title'              => $c->getTranslation('title', $locale),
                    'instructor'         => $instructorName ?: null,
                    'users_count'        => $enrolled,
                    'completion_percent' => $percent,
                    'status'             => $c->active ? 'active' : 'inactive',
                ];
            });

        return [
            'statistics'       => $statistics,
            'top_courses'      => $topCourses,
            'enrollment_trend' => $enrollmentTrend,
            'notifications'    => $this->repo->getRecentNotifications(8),
        ];
    }
}
