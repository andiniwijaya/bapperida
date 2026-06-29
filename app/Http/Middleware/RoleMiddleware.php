<?php

namespace App\Http\Middleware;

use App\Services\Auth\AccountStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts API routes to users with one of the allowed roles.
 *
 * Business rules:
 * - Unauthenticated requests receive 401 JSON.
 * - Pending, rejected, or inactive accounts receive 403 before role checks.
 * - Role list is supplied as middleware parameters (e.g. role:admin,superadmin).
 *
 * Related modules: Auth (AccountStatusService), User (role field).
 */
class RoleMiddleware
{
    public function __construct(
        protected AccountStatusService $accountStatusService,
    ) {
    }

    /**
     * Enforce authentication, active account status, and role membership.
     *
     * @param  Request  $request  Incoming HTTP request.
     * @param  Closure  $next  Next middleware/handler.
     * @param  string  ...$roles  Allowed role values (superadmin, admin, staff).
     * @return Response Continuation or JSON 401/403 error.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        if (! auth()->check()) {

            return response()->json([
                'success' => false,
                'message' => 'Anda belum masuk ke dalam sistem.',
            ], 401);
        }

        $user = auth()->user();
        $accessMessage = $this->accountStatusService->accessDeniedMessage($user);

        if ($accessMessage !== null) {
            return response()->json([
                'success' => false,
                'message' => $accessMessage,
            ], 403);
        }

        if (! in_array($user->role, $roles)) {

            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses.',
            ], 403);
        }

        return $next($request);
    }
}
