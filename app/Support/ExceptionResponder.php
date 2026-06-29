<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Maps HTTP and application exceptions to consistent web redirects or JSON error payloads.
 */
class ExceptionResponder
{
    public const SESSION_EXPIRED_FLASH = 'session_expired';

    public const SESSION_EXPIRED_MESSAGE = 'Sesi Anda telah berakhir. Silakan masuk kembali.';

    /**
     * Whether the request should receive a JSON error response instead of an HTML error page.
     */
    public static function expectsJsonResponse(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Build a standardized JSON error payload for API and AJAX consumers.
     */
    public static function jsonError(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Handle unauthenticated access.
     */
    public static function respondAuthentication(AuthenticationException $exception, Request $request): JsonResponse|RedirectResponse|null
    {
        if (self::expectsJsonResponse($request)) {
            return self::jsonError('Anda belum masuk ke dalam sistem.', 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle authorization failures.
     */
    public static function respondAuthorization(AuthorizationException $exception, Request $request): JsonResponse|null
    {
        if (! self::expectsJsonResponse($request)) {
            return null;
        }

        $message = $exception->getMessage();
        $message = is_string($message) && $message !== '' && ! Str::startsWith($message, 'This action')
            ? $message
            : 'Anda tidak memiliki hak akses untuk membuka halaman ini.';

        return self::jsonError($message, 403);
    }

    /**
     * Handle expired CSRF tokens / sessions.
     */
    public static function respondTokenMismatch(TokenMismatchException $exception, Request $request): JsonResponse|RedirectResponse
    {
        if (self::expectsJsonResponse($request)) {
            return self::jsonError(self::SESSION_EXPIRED_MESSAGE, 419);
        }

        return redirect()
            ->guest(route('login'))
            ->with(self::SESSION_EXPIRED_FLASH, true);
    }

    /**
     * Handle rate limiting responses.
     */
    public static function respondTooManyRequests(TooManyRequestsHttpException $exception, Request $request): JsonResponse|null
    {
        if (! self::expectsJsonResponse($request)) {
            return null;
        }

        return self::jsonError('Terlalu banyak permintaan. Silakan tunggu beberapa saat.', 429);
    }

    /**
     * Hide internal exception details from API consumers when debug mode is disabled.
     */
    public static function respondProductionApiFailure(Throwable $exception, Request $request): JsonResponse|null
    {
        if (config('app.debug') || ! self::expectsJsonResponse($request)) {
            return null;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return null;
        }

        return self::jsonError('Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.', 500);
    }
}
