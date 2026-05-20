<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getStatistics(): array;
    public function getTopCourses(int $limit): Collection;
    public function getEnrollmentTrend(int $days): array;

    /** @return array<int, array{title: string, detail?: string, time?: string, type?: string}> */
    public function getRecentNotifications(int $limit): array;
}
