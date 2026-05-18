<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface AttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateFiltered(int $perPage, array $filters): LengthAwarePaginator;

    public function countForUserInSection(int $userId, int $sectionId): int;
}
