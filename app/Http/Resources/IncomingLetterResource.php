<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of an incoming letter archive for API responses.
 *
 * Related modules: IncomingLetter, IncomingLetterPolicy.
 */
class IncomingLetterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'letter_number' => $this->letter_number,
            'sent_date' => $this->sent_date?->format('Y-m-d'),
            'received_date' => $this->received_date?->format('Y-m-d'),
            'disposition_date' => $this->disposition_date?->format('Y-m-d'),
            'sender' => $this->sender,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
                'code' => $this->department?->code,
            ],
            'disposition_department' => [
                'id' => $this->dispositionDepartment?->id,
                'name' => $this->dispositionDepartment?->name,
                'code' => $this->dispositionDepartment?->code,
            ],
            'subject' => $this->subject,
            'agenda_name' => $this->agenda_name,
            'summary' => $this->summary,
            'letter_attribute' => $this->letter_attribute,
            'letter_attribute_label' => config('letter.types')[$this->letter_attribute] ?? $this->letter_attribute,
            'attachment' => $this->attachment,
            'file_path' => $this->file_path,
            'file_name' => $this->file_path ? pathinfo($this->file_path, PATHINFO_BASENAME) : null,
            'status' => $this->status,
            'status_label' => config('status.incoming_letter')[$this->status] ?? $this->status,
            'notes' => $this->notes,
            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            ...($request->user()?->isSuperAdmin() || $request->user()?->isAdmin() ? [
                'deleted_at' => $this->deleted_at?->toDateTimeString(),
            ] : []),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'restore' => $request->user()?->can('restore', $this->resource) ?? false,
            ],
        ];
    }
}
