<?php

namespace App\Http\Controllers\AdminApi\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\Admin\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    protected PaymentMethodService $paymentMethodService;

    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $paginatedData = $this->paymentMethodService->getAll($perPage);

        return $this->success([
            'payment_methods' => PaymentMethodResource::collection($paginatedData),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
            ],
        ], 'messages.payment_method.retrieved');
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $paymentMethod = $this->paymentMethodService->create($request->validated());

        return $this->success(
            new PaymentMethodResource($paymentMethod),
            'messages.payment_method.created',
            201
        );
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return $this->success(
            new PaymentMethodResource($paymentMethod),
            'messages.payment_method.retrieved_single'
        );
    }

    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod
    ): JsonResponse {
        $paymentMethod = $this->paymentMethodService->update(
            $paymentMethod,
            $request->validated()
        );

        return $this->success(
            new PaymentMethodResource($paymentMethod),
            'messages.payment_method.updated'
        );
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->paymentMethodService->delete($paymentMethod);

        return $this->success(
            null,
            'messages.payment_method.deleted'
        );
    }
}
