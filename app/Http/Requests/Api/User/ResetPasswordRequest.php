<?php

namespace App\Http\Requests\Api\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorizes Super Admin password reset; no request body validation required.
 *
 * Related modules: User (update policy), ResetUserPasswordService.
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Requires update permission on the route-bound user.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * No input fields; reset is triggered by the route action alone.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
