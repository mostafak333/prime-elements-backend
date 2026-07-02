<?php

namespace App\Services;

use App\Models\DeliveryMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryMethodService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return DeliveryMethod::latest()->paginate($perPage);
    }

    public function create(array $data): DeliveryMethod
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;

        return DeliveryMethod::create($data);
    }

    public function update(DeliveryMethod $deliveryMethod, array $data): DeliveryMethod
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['updated_by'] = $adminId;

        $deliveryMethod->update($data);

        return $deliveryMethod->fresh();
    }

    public function delete(DeliveryMethod $deliveryMethod): void
    {
        $deliveryMethod->delete();
    }
}
