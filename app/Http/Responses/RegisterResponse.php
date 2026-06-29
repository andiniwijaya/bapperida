<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

/**
 * Fortify web registration redirect after a new user is created.
 *
 * Business rules:
 * - Redirects to the email verification notice, not the dashboard.
 * - Used by the web registration flow; API registration uses AuthController.
 *
 * Related modules: Fortify (CreateNewUser), Auth (RegisterService).
 */
class RegisterResponse implements RegisterResponseContract
{
    /**
     * Redirect the registrant to verify their email address.
     *
     * @param  \Illuminate\Http\Request  $request  Fortify registration request.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        return redirect()
            ->route('verification.notice')
            ->with(
                'status',
                'Registrasi berhasil. Silakan verifikasi email Anda.'
            );
    }
}
