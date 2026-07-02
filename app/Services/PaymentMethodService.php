<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentMethodService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return PaymentMethod::latest()->paginate($perPage);
    }

    public function create(array $data): PaymentMethod
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;

        return PaymentMethod::create($data);
    }

    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['updated_by'] = $adminId;

        $paymentMethod->update($data);

        return $paymentMethod->fresh();
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->delete();
    }
}
