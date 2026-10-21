<?php

namespace App\Http\Controllers\UserApi\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Order\StoreOrderRequest;
use App\Http\Resources\User\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $orders = $this->orderService->getUserOrders($perPage);

        return $this->success([
            'orders' => OrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 'messages.order.retrieved');
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->guard('api-user')->id()) {
            return $this->error('messages.order.not_found', 404);
        }

        $order->load(['orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail']);

        return $this->success(
            new OrderResource($order),
            'messages.order.retrieved_single'
        );
    }

    public function cancel(Order $order): JsonResponse
    {
        try {
            $cancelled = $this->orderService->cancelOrderForUser($order);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? __('messages.order.cannot_cancel');

            return $this->error($message, $e->status ?? 422);
        }

        return $this->success(
            new OrderResource($cancelled),
            'messages.order.cancelled'
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return $this->success(
            new OrderResource($order),
            'messages.order.placed',
            201
        );
    }
}
