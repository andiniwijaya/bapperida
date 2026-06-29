<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of a letter number registration for API responses.
 *
 * Business rules:
 * - deleted_at visible only to superadmin and admin roles.
 * - Includes localized type/status labels and policy-driven `can` flags.
 *
 * Related modules: LetterNumberRegistration (model, policy), Department, User.
 */
class LetterNumberRegistrationResource extends JsonResource
{
    /**
     * Transform the registration into an API-safe array.
     *
     * @param  Request  $request  Current HTTP request for authorization checks.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'index_code' => $this->index_code,

            'letter_code' => $this->letter_code,

            'sequence_number' => $this->sequence_number,

            'year' => $this->year,

            'letter_number' => $this->letter_number,

            'subject' => $this->subject,

            'summary' => $this->summary,

            'recipient' => $this->recipient,

            'letter_date' => $this->letter_date?->format('Y-m-d'),

            'letter_type' => $this->letter_type,

            'attachment' => $this->attachment,

            'notes' => $this->notes,

            'status' => $this->status,

            'department' => [
                'id' => $this->department?->id,
                'code' => $this->department?->code,
                'name' => $this->department?->name,
            ],

            'letter_type_label' => config('letter.types')[$this->letter_type] ?? $this->letter_type,
            'status_label' => config('status.letter_registration')[$this->status] ?? $this->status,

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