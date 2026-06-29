<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Updates the authenticated user's profile fields and avatar.
 *
 * Business rules:
 * - Email change clears email_verified_at (re-verification required).
 * - Username and email are normalized to lowercase.
 * - Replaces avatar on disk and deletes the previous file when present.
 *
 * Related modules: User, Auth (AuthController::updateProfile).
 */
class UpdateProfileService
{
    /**
     * Apply profile updates and handle avatar storage.
     *
     * @param  User  $user  Authenticated user.
     * @param  array<string, mixed>  $data  Validated name, email, username, optional avatar upload.
     * @return User Refreshed user model.
     */
    public function handle(User $user, array $data): User
    {
        if (isset($data['avatar'])) {
            $data['avatar'] = $data['avatar']->store(
                'avatars',
                'public'
            );

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        if (array_key_exists('email', $data) && strtolower($data['email']) !== $user->email) {
            $data['email_verified_at'] = null;
        }

        if (isset($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }

        if (isset($data['username'])) {
            $data['username'] = strtolower($data['username']);
        }

        $user->update(collect($data)->only([
            'name',
            'email',
            'username',
            'avatar',
        ])->all());

        if (array_key_exists('email_verified_at', $data)) {
            $user->email_verified_at = $data['email_verified_at'];
            $user->save();
        }

        return $user->fresh();
    }
}
