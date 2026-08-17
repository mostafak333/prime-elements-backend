<?php

namespace App\Http\Controllers\UserApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubscribeRequest;
use App\Http\Requests\User\UnsubscribeRequest;
use App\Models\User;
use App\Services\EmailSubscriptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewsletterController extends Controller
{
    use ApiResponse;

    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {}

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $user = $this->resolveUser();

        $this->subscriptionService->subscribe($request->input('email'), $user);

        return $this->success(null, 'Subscribed successfully.');
    }

    public function unsubscribe(UnsubscribeRequest $request): JsonResponse
    {
        $this->subscriptionService->unsubscribe($request->input('email'));

        return $this->success(null, 'Subscription preference updated.');
    }

    private function resolveUser(): ?User
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }
}
