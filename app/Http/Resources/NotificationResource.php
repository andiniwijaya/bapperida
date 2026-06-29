<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a database notification.
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'module' => $data['module'] ?? null,
            'action' => $data['action'] ?? null,
            'url' => $data['url'] ?? null,
            'metadata' => $data['metadata'] ?? [],
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
