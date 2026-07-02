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
        $data['created_by'] = auth('admin')->id();
        $data['updated_by'] = auth('admin')->id();

        return PaymentMethod::create($data);
    }

    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $data['updated_by'] = auth('admin')->id();

        $paymentMethod->update($data);

        return $paymentMethod->fresh();
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        $paymentMethod->delete();
    }
}