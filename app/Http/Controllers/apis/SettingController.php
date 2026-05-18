<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function __construct(private readonly SettingService $settingService) {}

    /** Public: return all non-file settings as key => value map. */
    public function index(): JsonResponse
    {
        return $this->success(__('messages.retrieved'), $this->settingService->getMap());
    }

    /** Admin: return full settings list (including type metadata for UI). */
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

    /** Admin: update one or many settings via { settings: { key: value } } payload. */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settingService->updateMany($request->validated('settings'));

        return $this->success(__('messages.updated'), $this->settingService->getMap());
    }
}
