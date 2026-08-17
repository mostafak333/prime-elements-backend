<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Faq::orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function getActiveFaqs(): Collection
    {
        return Faq::active()
            ->ordered()
            ->get();
    }

    public function create(array $data): Faq
    {
        $faq = Faq::create($data);

        return $faq->fresh();
    }

    public function update(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq->fresh();
    }

    public function delete(Faq $faq): void
    {
        $faq->delete();
    }
}
