<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of an outgoing letter archive for API responses.
 *
 * Business rules:
 * - Embeds nested registration and department summary.
 * - deleted_at visible only to superadmin and admin.
 * - Includes policy-driven `can` flags for frontend actions.
 *
 * Related modules: OutgoingLetter (model, policy), LetterNumberRegistration, Department.
 */
class OutgoingLetterResource extends JsonResource
{
    /**
     * Transform the outgoing letter into an API-safe array.
     *
     * @param  Request  $request  Current HTTP request for authorization checks.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'letter_number_registration_id' => $this->letter_number_registration_id,
            'letter_type' => $this->letter_type,
            'letter_type_label' => config('letter.types')[$this->letter_type] ?? $this->letter_type,
            'attachment' => $this->attachment,
            'file_path' => $this->file_path,
            'status' => $this->status,
            'status_label' => config('status.outgoing_letter')[$this->status] ?? $this->status,
            'notes' => $this->notes,
            'file_name' => $this->file_path ? pathinfo($this->file_path, PATHINFO_BASENAME) : null,
            'registration' => [
                'id' => $this->registration?->id,
                'letter_number' => $this->registration?->letter_number,
                'index_code' => $this->registration?->index_code,
                'letter_code' => $this->registration?->letter_code,
                'sequence_number' => $this->registration?->sequence_number,
                'year' => $this->registration?->year,
                'department' => [
                    'id' => $this->registration?->department?->id,
                    'code' => $this->registration?->department?->code,
                    'name' => $this->registration?->department?->name,
                ],
                'subject' => $this->registration?->subject,
                'summary' => $this->registration?->summary,
                'recipient' => $this->registration?->recipient,
                'letter_date' => $this->registration?->letter_date?->format('Y-m-d'),
            ],
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
