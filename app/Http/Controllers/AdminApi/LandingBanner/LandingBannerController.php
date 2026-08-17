<?php

namespace App\Http\Controllers\AdminApi\LandingBanner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingBanner\CreateLandingBannerRequest;
use App\Http\Requests\Admin\LandingBanner\UpdateLandingBannerRequest;
use App\Http\Resources\Admin\AdminLandingBannerResource;
use App\Models\LandingBanner;
use App\Services\LandingBannerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingBannerController extends Controller
{
    use ApiResponse;

    protected LandingBannerService $bannerService;

    public function __construct(LandingBannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $banners = $this->bannerService->getAll($perPage);

        return $this->success([
            'banners' => AdminLandingBannerResource::collection($banners),
            'pagination' => [
                'total' => $banners->total(),
                'per_page' => $banners->perPage(),
                'current_page' => $banners->currentPage(),
                'last_page' => $banners->lastPage(),
            ],
        ], 'Banners retrieved successfully.');
    }

    public function store(CreateLandingBannerRequest $request): JsonResponse
    {
        $banner = $this->bannerService->create($request->validated());

        return $this->success(
            new AdminLandingBannerResource($banner),
            'Banner created successfully.',
            201
        );
    }

    public function show(LandingBanner $landing_banner): JsonResponse
    {
        return $this->success(
            new AdminLandingBannerResource($landing_banner),
            'Banner retrieved successfully.'
        );
    }

    public function update(UpdateLandingBannerRequest $request, LandingBanner $landing_banner): JsonResponse
    {
        $banner = $this->bannerService->update($landing_banner, $request->validated());

        return $this->success(
            new AdminLandingBannerResource($banner),
            'Banner updated successfully.'
        );
    }

    public function destroy(LandingBanner $landing_banner): JsonResponse
    {
        $this->bannerService->delete($landing_banner);

        return $this->success(null, 'Banner deleted successfully.');
    }
}
