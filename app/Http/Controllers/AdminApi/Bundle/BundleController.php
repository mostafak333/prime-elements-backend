<?php

namespace App\Http\Controllers\AdminApi\Bundle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Bundle\StoreBundleRequest;
use App\Http\Requests\Admin\Bundle\UpdateBundleRequest;
use App\Http\Resources\Admin\AdminBundleResource;
use App\Models\Bundle;
use App\Services\BundleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BundleService $bundleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'status' => $request->filled('status') ? $request->boolean('status') : null,
            'search' => $request->get('search'),
        ];

        $bundles = $this->bundleService->getAll($filters, $perPage);

        return $this->success([
            'bundles' => AdminBundleResource::collection($bundles),
            'pagination' => [
                'total' => $bundles->total(),
                'per_page' => $bundles->perPage(),
                'current_page' => $bundles->currentPage(),
                'last_page' => $bundles->lastPage(),
            ],
        ], 'messages.bundle.retrieved');
    }

    public function store(StoreBundleRequest $request): JsonResponse
    {
        $bundle = $this->bundleService->create($request->validated());

        return $this->success(
            new AdminBundleResource($bundle),
            'messages.bundle.created',
            201
        );
    }

    public function show(Bundle $bundle): JsonResponse
    {
        $bundle->load(['items.product.images', 'items.product.detail'])->loadCount('items');

        return $this->success(
            new AdminBundleResource($bundle),
            'messages.bundle.retrieved_single'
        );
    }

    public function update(UpdateBundleRequest $request, Bundle $bundle): JsonResponse
    {
        $bundle = $this->bundleService->update($bundle, $request->validated());

        return $this->success(
            new AdminBundleResource($bundle),
            'messages.bundle.updated'
        );
    }

    public function destroy(Bundle $bundle): JsonResponse
    {
        $this->bundleService->delete($bundle);

        return $this->success(null, 'messages.bundle.deleted');
    }

    public function updateStatus(Bundle $bundle): JsonResponse
    {
        $bundle = $this->bundleService->updateStatus($bundle);

        return $this->success(
            new AdminBundleResource($bundle),
            'messages.bundle.status_updated'
        );
    }
}
