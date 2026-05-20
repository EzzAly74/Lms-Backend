<?php

namespace App\Repositories\Eloquents;

use App\Models\JobTitle;
use App\Repositories\Contracts\JobTitleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class JobTitleRepository extends BaseRepository implements JobTitleRepositoryInterface
{
    public function __construct(JobTitle $model)
    {
        parent::__construct($model);
    }

    public function list(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->withCount('qualificationSkills')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return $this->model->newQuery()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    public function syncQualifications(JobTitle $jobTitle, array $qualIds): void
    {
        $jobTitle->qualificationSkills()->sync($qualIds);
    }
}
