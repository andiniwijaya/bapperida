<?php

use App\Support\ExceptionResponder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => ExceptionResponder::expectsJsonResponse($request),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return ExceptionResponder::respondAuthentication($exception, $request);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return ExceptionResponder::respondAuthorization($exception, $request);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            if (! ExceptionResponder::expectsJsonResponse($request)) {
                return null;
            }

            return ExceptionResponder::jsonError(
                'Anda tidak memiliki hak akses untuk membuka halaman ini.',
                403
            );
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            return ExceptionResponder::respondTokenMismatch($exception, $request);
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            return ExceptionResponder::respondTooManyRequests($exception, $request);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            return ExceptionResponder::respondProductionApiFailure($exception, $request);
        });
    })->create();
