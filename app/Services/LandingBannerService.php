<?php

namespace App\Services;

use App\Models\LandingBanner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class LandingBannerService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return LandingBanner::orderBy('sort_order')
            ->paginate($perPage);
    }

    public function create(array $data): LandingBanner
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->mediaService->store($data['image'], 'banners');
        }

        return LandingBanner::create($data);
    }

    public function update(LandingBanner $banner, array $data): LandingBanner
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->mediaService->replace(
                $data['image'],
                $banner->image,
                'banners'
            );
        }

        $banner->update($data);

        return $banner->refresh();
    }

    public function delete(LandingBanner $banner): void
    {
        $this->mediaService->delete($banner->image);
        $banner->delete();
    }

    public function getActiveBanners(): Collection
    {
        return LandingBanner::where('status', true)
            ->orderBy('sort_order')
            ->get();
    }
}
