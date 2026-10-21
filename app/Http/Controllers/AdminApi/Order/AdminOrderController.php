<?php

namespace App\Http\Controllers\AdminApi\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    use ApiResponse;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'user_id', 'status', 'date_from', 'date_to']);
        $perPage = (int) $request->get('per_page', 15);

        $orders = $this->orderService->getAll($filters, $perPage);

        return $this->success([
            'orders' => AdminOrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ], 'messages.order.retrieved');
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        return $this->success(
            new AdminOrderResource($order),
            'messages.order.retrieved_single'
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $order)
    {
        $order = Order::find($order);

        if (! $order) {
            return $this->error('messages.order.not_found', 404);
        }

        $updatedOrder = $this->orderService->updateStatus(
            $order,
            $request->status
        );

        return $this->success(
            new AdminOrderResource($updatedOrder),
            'messages.order.status_updated'
        );
    }
}
