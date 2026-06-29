<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

/**
 * Fortify redirect after a successful password reset via email link.
 *
 * Business rules:
 * - Does not auto-login the user after password reset.
 * - Redirects to a dedicated success page with guidance to login manually.
 */
class PasswordResetResponse implements PasswordResetResponseContract
{
    /**
     * @param  string  $status  Fortify translation status key.
     */
    public function __construct(protected string $status) {}

    /**
     * Redirect to the password reset success page or return JSON for API clients.
     *
     * @param  \Illuminate\Http\Request  $request  Fortify password update request.
     */
    public function toResponse($request)
    {
        $message = 'Kata sandi berhasil dibuat. Silakan masuk menggunakan alamat email dan kata sandi yang baru Anda buat.';

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message], 200);
        }

        return redirect()
            ->route('password.reset.success')
            ->with('status', $message);
    }
}
