<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repo
    ) {}

    /**
     * @param  'week'|'month'|'quarter'|'year'|null  $range
     */
    public function getSummary(?string $range = null): array
    {
        $statistics      = $this->repo->getStatistics();
        $resolvedRange   = $this->resolveRange($range);
        $enrollmentTrend = $this->repo->getEnrollmentTrendByRange($resolvedRange);
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
            'trend_range'      => $resolvedRange,
            'notifications'    => $this->repo->getRecentNotifications(8),
        ];
    }

    private function resolveRange(?string $range): string
    {
        return match ($range) {
            'week', 'quarter', 'year' => $range,
            default                   => 'month',
        };
    }
}
