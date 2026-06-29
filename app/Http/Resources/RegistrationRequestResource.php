<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'username' => $this->user?->username,
            ],
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at?->toDateTimeString(),
            'approved_by' => [
                'id' => $this->approver?->id,
                'name' => $this->approver?->name,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'approve' => $request->user()?->can('approve', $this->resource) ?? false,
                'reject' => $request->user()?->can('reject', $this->resource) ?? false,
            ],
        ];
    }
}
