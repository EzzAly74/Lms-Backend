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
        $statistics = $this->repo->getStatistics();
        $topCourses = $this->repo->getTopCourses(10)
            ->map(fn ($c) => [
                'id'          => $c->id,
                'title'       => $c->getTranslation('title', app()->getLocale()),
                'users_count' => $c->users_count,
            ]);

        return [
            'statistics'  => $statistics,
            'top_courses' => $topCourses,
        ];
    }
}
