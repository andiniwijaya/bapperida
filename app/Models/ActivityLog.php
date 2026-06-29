<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit trail entry for security and compliance.
 *
 * Business rules:
 * - Append-only: created via RecordActivityLogService, never updated or deleted via API.
 * - Snapshots actor role and department at log time for historical accuracy.
 * - entity_type and entity_id reference the affected domain record when applicable.
 *
 * Related modules: RecordActivityLogService, ActivityLogPolicy, all business services.
 */
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    /**
     * Mass-assignable audit fields. Logs are created exclusively via RecordActivityLogService.
     */
    protected $fillable = [
        'user_id',
        'user_role',
        'department_id',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'description',
        'url',
        'method',
        'ip_address',
        'browser',
        'platform',
        'device',
        'user_agent',
        'properties',
        'logged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'logged_at' => 'datetime',
            'department_id' => 'integer',
            'entity_id' => 'integer',
        ];
    }

    /**
     * User who performed the audited action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Department snapshot of the actor at log time.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
