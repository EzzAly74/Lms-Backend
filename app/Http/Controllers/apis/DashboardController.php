<?php

namespace App\Http\Controllers\apis;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    public function __construct(private readonly DashboardService $service) {}

    public function index(): JsonResponse
    {
        return $this->success(__('messages.retrieved'), $this->service->getSummary());
    }
}
