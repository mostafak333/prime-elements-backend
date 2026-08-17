<?php

namespace App\Http\Controllers\AdminApi\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Http\Resources\Admin\FaqResource;
use App\Models\Faq;
use App\Services\FaqService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    protected FaqService $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $paginatedData = $this->faqService->getAll($perPage);

        return $this->success([
            'faqs' => FaqResource::collection($paginatedData),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
            ],
        ], 'FAQs retrieved successfully.');
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = $this->faqService->create($request->validated());

        return $this->success(
            new FaqResource($faq),
            'FAQ created successfully.',
            201
        );
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->success(
            new FaqResource($faq),
            'FAQ retrieved successfully.'
        );
    }

    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq = $this->faqService->update(
            $faq,
            $request->validated()
        );

        return $this->success(
            new FaqResource($faq),
            'FAQ updated successfully.'
        );
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->faqService->delete($faq);

        return $this->success(
            null,
            'FAQ deleted successfully.'
        );
    }

    public function updateStatus(Faq $faq): JsonResponse
    {
        $faq = $this->faqService->update(
            $faq,
            ['is_active' => ! $faq->is_active]
        );

        return $this->success(
            new FaqResource($faq),
            'FAQ status updated successfully.'
        );
    }
}
