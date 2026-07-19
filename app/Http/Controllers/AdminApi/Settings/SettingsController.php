<?php

namespace App\Http\Controllers\AdminApi\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingsRequest;
use App\Http\Resources\Admin\SettingsResource;
use App\Services\SettingsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;


class SettingsController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getSettings();

        return $this->success(
            new SettingsResource($settings),
            'Settings retrieved successfully.'
        );
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $settings = $this->settingsService->updateSettings(
            $request->validated()
        );

        return $this->success(
            new SettingsResource($settings),
            'Settings updated successfully.'
        );
    }
}
