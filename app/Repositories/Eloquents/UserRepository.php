<?php

namespace App\Repositories\Eloquents;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginateWithSearch(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('machine_code', 'LIKE', "%{$search}%")
                    ->orWhere('department_name', 'LIKE', "%{$search}%");
            }))
            ->orderBy('system_id')
            ->paginate($perPage);
    }

    public function findBySystemId(string $systemId): ?User
    {
        return $this->model->newQuery()->where('system_id', $systemId)->first();
    }

    public function updateOrCreateBySystemId(string $systemId, array $data): User
    {
        return $this->model->newQuery()->updateOrCreate(
            ['system_id' => $systemId],
            $data,
        );
    }

    public function findWithRoles(int $id): User
    {
        return $this->model->newQuery()->with('roles')->findOrFail($id);
    }

    public function findWithActivity(int $id): User
    {
        return $this->model->newQuery()
            ->with([
                'courses.category:id,name',
                'ratings.course:id,title',
                'exams.course:id,title,certificate',
                'exams.exam:id,title,degree,is_final',
            ])
            ->findOrFail($id);
    }
}
