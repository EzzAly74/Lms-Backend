<?php

namespace App\Repositories\Eloquents;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    public function findWithRoles(int $id): Admin
    {
        return $this->model->newQuery()->with('roles')->findOrFail($id);
    }

    public function paginateWithSearch(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('roles')
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByEmail(string $email): ?Admin
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }
}
