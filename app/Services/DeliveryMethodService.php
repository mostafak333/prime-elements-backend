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
        $data['created_by'] = auth('admin')->id();
        $data['updated_by'] = auth('admin')->id();

        return DeliveryMethod::create($data);
    }

    public function update(DeliveryMethod $deliveryMethod, array $data): DeliveryMethod
    {
        $data['updated_by'] = auth('admin')->id();

        $deliveryMethod->update($data);

        return $deliveryMethod->fresh();
    }

    public function delete(DeliveryMethod $deliveryMethod): void
    {
        $deliveryMethod->delete();
    }
}