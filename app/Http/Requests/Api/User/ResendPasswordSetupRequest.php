<?php

namespace App\Http\Requests\Api\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorizes resending password-setup email for users pending onboarding.
 */
class ResendPasswordSetupRequest extends FormRequest
{
    /**
     * Requires resendPasswordSetup permission on the route-bound user.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('resendPasswordSetup', $user) ?? false);
    }

    /**
     * No input fields; resend is triggered by the route action alone.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
