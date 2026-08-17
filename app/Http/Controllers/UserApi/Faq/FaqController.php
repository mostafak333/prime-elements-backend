<?php

namespace App\Http\Controllers\UserApi\Faq;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\FaqResource;
use App\Services\FaqService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    use ApiResponse;

    public function __construct(
        private FaqService $faqService
    ) {}

    public function index(): JsonResponse
    {
        $faqs = $this->faqService->getActiveFaqs();

        return $this->success([
            'faqs' => FaqResource::collection($faqs),
        ], 'FAQs retrieved successfully.');
    }
}
