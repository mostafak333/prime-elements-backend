<?php

namespace App\Http\Controllers\UserApi\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PolicyController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $settings = Setting::first() ?? new Setting;

        return $this->success([
            'terms_conditions' => $settings->terms_conditions,
            'privacy_policy' => $settings->privacy_policy,
            'return_exchange_policy' => $settings->return_exchange_policy,
        ], 'Policies retrieved successfully.');
    }
}
