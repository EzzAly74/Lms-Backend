<?php

namespace App\Repositories\Eloquents;

use App\Models\QualificationSkill;
use App\Repositories\Contracts\QualificationSkillRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class QualificationSkillRepository extends BaseRepository implements QualificationSkillRepositoryInterface
{
    public function __construct(QualificationSkill $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->withCount('courses')
            ->latest()
            ->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return $this->model->newQuery()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get();
    }
}
