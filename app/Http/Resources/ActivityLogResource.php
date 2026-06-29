<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of an immutable audit log entry.
 *
 * Audit trail: exposes full actor context and entity reference for investigations.
 */
class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'user_role' => $this->user_role,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
            ],
            'action' => $this->action,
            'module' => $this->module,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'description' => $this->description,
            'url' => $this->url,
            'method' => $this->method,
            'ip_address' => $this->ip_address,
            'browser' => $this->browser,
            'platform' => $this->platform,
            'device' => $this->device,
            'user_agent' => $this->user_agent,
            'properties' => $this->properties,
            'logged_at' => $this->logged_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
