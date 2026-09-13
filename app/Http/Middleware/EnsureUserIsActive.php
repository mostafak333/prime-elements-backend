<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = auth()->guard('api-user');
        $user = $guard->user();

        if (! $user) {
            return $this->deny('Unauthenticated.', 401);
        }

        if ($user->status === 'blocked') {
            $this->endSession($guard);

            return $this->deny('Your account has been blocked. Please contact support.', 403);
        }

        $tokenVersion = (int) $guard->payload()->get('token_version');

        if ($tokenVersion !== (int) $user->token_version) {
            $this->endSession($guard);

            return $this->deny('Your session has been ended. Please log in again.', 401);
        }

        return $next($request);
    }

    private function endSession($guard): void
    {
        try {
            $guard->logout();
        } catch (\Throwable $e) {
            // The token may already be invalid; the response below still applies.
        }
    }

    private function deny(string $message, int $status): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
