<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getStatistics(): array;
    public function getTopCourses(int $limit): Collection;
}
