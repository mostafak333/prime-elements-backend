<?php

namespace App\Http\Controllers\AdminApi\DeliveryMethod;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryMethod\{
    StoreDeliveryMethodRequest,
    UpdateDeliveryMethodRequest
};
use App\Http\Resources\Admin\DeliveryMethodResource;
use App\Models\DeliveryMethod;
use App\Services\DeliveryMethodService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryMethodController extends Controller
{
    use ApiResponse;

    protected DeliveryMethodService $deliveryMethodService;

    public function __construct(DeliveryMethodService $deliveryMethodService)
    {
        $this->deliveryMethodService = $deliveryMethodService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $paginatedData = $this->deliveryMethodService->getAll($perPage);

        return $this->success([
            'delivery_methods' => DeliveryMethodResource::collection($paginatedData),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page'    => $paginatedData->lastPage(),
                'per_page'     => $paginatedData->perPage(),
                'total'        => $paginatedData->total(),
            ]
        ], 'Delivery methods retrieved successfully.');
    }

    public function store(StoreDeliveryMethodRequest $request): JsonResponse
    {
        $deliveryMethod = $this->deliveryMethodService->create($request->validated());

        return $this->success(
            new DeliveryMethodResource($deliveryMethod),
            'Delivery method created successfully.',
            201
        );
    }

    public function show(DeliveryMethod $deliveryMethod): JsonResponse
    {
        return $this->success(
            new DeliveryMethodResource($deliveryMethod),
            'Delivery method retrieved successfully.'
        );
    }

    public function update(
        UpdateDeliveryMethodRequest $request,
        DeliveryMethod $deliveryMethod
    ): JsonResponse {

        $deliveryMethod = $this->deliveryMethodService->update(
            $deliveryMethod,
            $request->validated()
        );

        return $this->success(
            new DeliveryMethodResource($deliveryMethod),
            'Delivery method updated successfully.'
        );
    }

    public function destroy(DeliveryMethod $deliveryMethod): JsonResponse
    {
        $this->deliveryMethodService->delete($deliveryMethod);

        return $this->success(
            null,
            'Delivery method deleted successfully.'
        );
    }
}