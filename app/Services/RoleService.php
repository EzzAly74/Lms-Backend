<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $repo
    ) {}

    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->repo->paginateWithSearch($perPage, $search);
    }

    public function all(): Collection
    {
        return $this->repo->all();
    }

    public function find(int $id): Role
    {
        return $this->repo->findOrFail($id);
    }

    public function create(string $name, array $permissions = []): Role
    {
        $role = $this->repo->create($name);
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }
        return $this->repo->findOrFail($role->id);
    }

    public function update(Role $role, string $name, array $permissions = []): Role
    {
        $role = $this->repo->update($role, $name);
        $role->syncPermissions($permissions);
        return $this->repo->findOrFail($role->id);
    }

    public function delete(Role $role): void
    {
        $this->repo->delete($role);
    }
}
