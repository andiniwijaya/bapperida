<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

/**
 * Fortify response after a user verifies their email address.
 *
 * Business rules:
 * - Pending or rejected staff are logged out and sent to registration success.
 * - Verified active users follow Fortify's intended redirect with ?verified=1.
 * - JSON clients receive 204 No Content.
 *
 * Related modules: Fortify, User (status), Auth (account approval workflow).
 */
class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * Route the user based on account status after email verification.
     *
     * @param  \Illuminate\Http\Request  $request  Verification request.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        $user = $request->user();

        if ($user !== null && ($user->isPending() || $user->isRejected())) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('register.success')
                ->with(
                    'status',
                    'Email berhasil diverifikasi. Akun Anda menunggu persetujuan Super Admin.'
                );
        }

        return redirect()->intended(Fortify::redirects('email-verification').'?verified=1');
    }
}
