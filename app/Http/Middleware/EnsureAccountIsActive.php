<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccountStatusService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks pending, rejected, inactive, or unverified accounts from protected routes.
 *
 * Web requests redirect with flash errors; API/AJAX requests receive JSON 403/401.
 */
class EnsureAccountIsActive
{
    public function __construct(
        protected AccountStatusService $accountStatusService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $this->unauthenticatedResponse($request);
        }

        if (! $user->hasVerifiedEmail()) {
            $this->invalidateAuthenticatedSession($request);

            return $this->deniedResponse(
                $request,
                'Silakan verifikasi email terlebih dahulu.',
                route('verification.notice'),
            );
        }

        $accessMessage = $this->accountStatusService->accessDeniedMessage($user);

        if ($accessMessage !== null) {
            $this->invalidateAuthenticatedSession($request);

            return $this->deniedResponse($request, $accessMessage, route('login'));
        }

        return $next($request);
    }

    /**
     * @return Response Redirect or JSON 401.
     */
    private function unauthenticatedResponse(Request $request): Response
    {
        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum masuk ke dalam sistem.',
            ], 401);
        }

        return redirect()->route('login');
    }

    /**
     * @return Response Redirect or JSON 403.
     */
    private function deniedResponse(Request $request, string $message, string $redirectRoute): Response
    {
        if ($this->expectsJsonResponse($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()
            ->to($redirectRoute)
            ->withErrors(['login' => $message]);
    }

    private function expectsJsonResponse(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Invalidate the web session without calling logout on token guards.
     */
    private function invalidateAuthenticatedSession(Request $request): void
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
