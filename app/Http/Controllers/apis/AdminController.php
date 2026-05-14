<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends ApiController
{
    public function __construct(private readonly AdminService $service) {}

    public function index(Request $request): JsonResponse
    {
        $admins = $this->service->paginate(
            (int) $request->get('per_page', 20),
            $request->get('search')
        );
        return $this->paginated(__('messages.retrieved'), $admins);
    }

    public function show(Admin $admin): JsonResponse
    {
        $admin = $this->service->findWithRoles($admin->id);
        return $this->success(__('messages.retrieved'), new AdminResource($admin));
    }

    public function store(AdminRequest $request): JsonResponse
    {
        $admin = $this->service->create($request->validated());
        return $this->created(__('messages.created'), new AdminResource($admin));
    }

    public function update(Admin $admin, AdminRequest $request): JsonResponse
    {
        $admin = $this->service->update($admin, $request->validated());
        return $this->success(__('messages.updated'), new AdminResource($admin));
    }

    public function destroy(Admin $admin): JsonResponse
    {
        $this->service->delete($admin);
        return $this->deleted(__('messages.deleted'));
    }
}
