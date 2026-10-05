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
            'terms_conditions_en' => $settings->terms_conditions_en,
            'terms_conditions_ar' => $settings->terms_conditions_ar,
            'privacy_policy_en' => $settings->privacy_policy_en,
            'privacy_policy_ar' => $settings->privacy_policy_ar,
            'return_exchange_policy_en' => $settings->return_exchange_policy_en,
            'return_exchange_policy_ar' => $settings->return_exchange_policy_ar,
        ], 'Policies retrieved successfully.');
    }
}
