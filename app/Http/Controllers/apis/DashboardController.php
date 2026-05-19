<?php

namespace App\Http\Controllers\apis;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class DashboardController extends ApiController
{
    public function __construct(private readonly DashboardService $service) {}

    /**
     * @OA\Get(
     *     path="/dashboard",
     *     tags={"Dashboard"},
     *     summary="Admin dashboard summary (counts, recent activity).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard summary",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="result",
     *                         type="object",
     *                         additionalProperties=true
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden")
     * )
     */
    public function index(): JsonResponse
    {
        return $this->success(__('messages.retrieved'), $this->service->getSummary());
    }
}
