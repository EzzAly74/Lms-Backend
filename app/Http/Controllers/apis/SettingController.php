<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SettingController extends ApiController
{
    public function __construct(private readonly SettingService $settingService) {}

    /**
     * @OA\Get(
     *     path="/settings",
     *     tags={"Settings"},
     *     summary="Get public settings as a key=>value map. Public.",
     *     @OA\Response(
     *         response=200,
     *         description="Settings map",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(
     *                     property="result",
     *                     type="object",
     *                     additionalProperties=@OA\AdditionalProperties(type="string"),
     *                     example={"site_name":"2B Academy","support_email":"info@example.com"}
     *                 ))
     *             }
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return $this->success(__('messages.retrieved'), $this->settingService->getMap());
    }

    /**
     * @OA\Get(
     *     path="/admin/settings",
     *     tags={"Settings"},
     *     summary="Get the full settings list with type metadata (admin only).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Full settings list",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(
     *                     property="result",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id",    type="integer"),
     *                         @OA\Property(property="key",   type="string"),
     *                         @OA\Property(property="value", type="string", nullable=true),
     *                         @OA\Property(property="type",  type="string"),
     *                         @OA\Property(property="label", type="string")
     *                     )
     *                 ))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden")
     * )
     */
    public function adminIndex(): JsonResponse
    {
        $settings = $this->settingService->getAll()->map(fn ($s) => [
            'id'    => $s->id,
            'key'   => $s->key,
            'value' => $s->value,
            'type'  => $s->type,
            'label' => $s->label ?? $s->key,
        ]);

        return $this->success(__('messages.retrieved'), $settings);
    }

    /**
     * @OA\Put(
     *     path="/admin/settings",
     *     tags={"Settings"},
     *     summary="Update one or many settings (admin only).",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"settings"},
     *             @OA\Property(
     *                 property="settings",
     *                 type="object",
     *                 description="Map of setting key to string value.",
     *                 additionalProperties=@OA\AdditionalProperties(type="string", nullable=true),
     *                 example={"site_name":"2B Academy","support_email":"info@example.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated settings map",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(
     *                     property="result",
     *                     type="object",
     *                     additionalProperties=@OA\AdditionalProperties(type="string")
     *                 ))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settingService->updateMany($request->validated('settings'));

        return $this->success(__('messages.updated'), $this->settingService->getMap());
    }
}
