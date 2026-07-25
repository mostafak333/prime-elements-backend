<?php

namespace App\Services;

use App\Models\LandingBanner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LandingBannerService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return LandingBanner::orderBy('sort_order')
            ->paginate($perPage);
    }

    public function create(array $data): LandingBanner
    {
        return LandingBanner::create($data);
    }

    public function update(LandingBanner $banner, array $data): LandingBanner
    {
        $banner->update($data);
        return $banner->refresh();
    }

    public function delete(LandingBanner $banner): void
    {
        $banner->delete();
    }

    public function getActiveBanners(): Collection
    {
        return LandingBanner::where('status', true)
            ->orderBy('sort_order')
            ->get();
    }
}
