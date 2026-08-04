<?php

namespace App\Http\Controllers\UserApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserLandingBannerResource;
use App\Services\LandingBannerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LandingBannerService $bannerService
    ) {}

    public function index(): JsonResponse
    {
        $banners = $this->bannerService->getActiveBanners();

        return $this->success([
            'banners' => UserLandingBannerResource::collection($banners),
        ]);
    }
}
