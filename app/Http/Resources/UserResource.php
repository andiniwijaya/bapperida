<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * JSON representation of a User for API responses.
 *
 * Business rules:
 * - Exposes policy-driven `can` flags for frontend action buttons.
 * - Avatar URL is resolved from public disk storage when present.
 *
 * Related modules: User (model, policy), Department, Auth (login/me responses).
 */
class UserResource extends JsonResource
{
    /**
     * Transform the user model into an API-safe array.
     *
     * @param  Request  $request  Current HTTP request for authorization checks.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'status_label' => config('status.user')[$this->status] ?? $this->status,
            'department' => [
                'id' => $this->department?->id,
                'code' => $this->department?->code,
                'name' => $this->department?->name,
            ],
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar
                ? Storage::disk('public')->url($this->avatar)
                : null,
            'must_change_password' => (bool) $this->must_change_password,
            'password_onboarding_status' => $this->must_change_password ? 'pending' : 'completed',
            'password_onboarding_status_label' => config('status.user_password_onboarding')[
                $this->must_change_password ? 'pending' : 'completed'
            ],
            'last_login_at' => $this->last_login_at?->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'change_role' => $request->user()?->can('changeRole', $this->resource) ?? false,
                'change_status' => $request->user()?->can('changeStatus', $this->resource) ?? false,
                'resend_password_setup' => $request->user()?->can('resendPasswordSetup', $this->resource) ?? false,
            ],
        ];
    }
}
