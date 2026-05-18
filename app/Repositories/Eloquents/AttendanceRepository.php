<?php

namespace App\Repositories\Eloquents;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function paginateFiltered(int $perPage, array $filters): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when($filters['course_id'] ?? null, fn ($q, $v) => $q->where('course_id', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['section_id'] ?? null, fn ($q, $v) => $q->where('section_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate($perPage);
    }

    public function countForUserInSection(int $userId, int $sectionId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('section_id', $sectionId)
            ->count();
    }
}
