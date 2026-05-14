<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    public function __construct(private readonly RoleService $service) {}

    public function index(Request $request): JsonResponse
    {
        $roles = $this->service->paginate(
            (int) $request->get('per_page', 20),
            $request->get('search')
        );
        return $this->paginated(__('messages.retrieved'), $roles);
    }

    public function all(): JsonResponse
    {
        return $this->success(__('messages.retrieved'),
            RoleResource::collection($this->service->all())
        );
    }

    public function show(Role $role): JsonResponse
    {
        $role = $this->service->find($role->id);
        return $this->success(__('messages.retrieved'), new RoleResource($role));
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = $this->service->create($data['name'], $data['permissions'] ?? []);
        return $this->created(__('messages.created'), new RoleResource($role));
    }

    public function update(Role $role, RoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = $this->service->update($role, $data['name'], $data['permissions'] ?? []);
        return $this->success(__('messages.updated'), new RoleResource($role));
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->service->delete($role);
        return $this->deleted(__('messages.deleted'));
    }
}
