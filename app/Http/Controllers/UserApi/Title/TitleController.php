<?php

namespace App\Http\Controllers\UserApi\Title;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\TitleResource;
use App\Services\TitleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TitleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TitleService $titleService
    ) {}

    public function index(): JsonResponse
    {
        return $this->success([
            'titles' => TitleResource::collection(
                $this->titleService->getAllForUser()
            ),
        ]);
    }
}