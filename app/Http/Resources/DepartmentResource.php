<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of a Department for API responses.
 *
 * Business rules:
 * - Includes policy-driven `can` flags for UI action visibility.
 * - deleted_at exposed for superadmin restore workflows.
 *
 * Related modules: Department (model, policy), User, letter modules.
 */
class DepartmentResource extends JsonResource
{
    /**
     * Transform the department model into an API-safe array.
     *
     * @param  Request  $request  Current HTTP request for authorization checks.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'restore' => $request->user()?->can('restore', $this->resource) ?? false,
            ],
        ];
    }
}
