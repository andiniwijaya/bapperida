<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Central audit trail recorder for all application modules.
 *
 * Business rules:
 * - All significant actions must be logged through this service (not ad-hoc in controllers).
 * - Captures actor role, department, request metadata, and optional entity reference.
 * - Logs are append-only; mutation is blocked at policy layer.
 *
 * Audit trail: official source for compliance and security investigations.
 */
class RecordActivityLogService
{
    public function __construct(private ?Request $request = null) {}

    /**
     * Persist an immutable activity log entry.
     *
     * @param  string  $action  Normalized action key (e.g. incoming_letter_created).
     * @param  string  $module  Module namespace (e.g. incoming_letter, auth, user).
     * @param  string  $description  Human-readable audit description.
     * @param  Model|null  $entity  Affected Eloquent model when applicable.
     * @param  User|null  $actor  Acting user; defaults to authenticated user.
     * @param  array<string, mixed>|null  $properties  Additional structured audit payload.
     */
    public function record(
        string $action,
        string $module,
        string $description,
        ?Model $entity = null,
        ?User $actor = null,
        ?array $properties = null,
    ): ActivityLog {
        $actor = $actor ?? Auth::user();

        if (! $actor) {
            throw new \InvalidArgumentException('Activity log requires an acting user.');
        }

        $request = $this->request ?? request();

        return ActivityLog::create([
            'user_id' => $actor->id,
            'user_role' => $actor?->role,
            'department_id' => $actor?->department_id,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? $this->entityType($entity) : null,
            'entity_id' => $entity?->getKey(),
            'description' => $description,
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'ip_address' => $request?->ip(),
            'browser' => $request?->header('Sec-CH-UA-Platform'),
            'platform' => $request?->header('Sec-CH-UA-Platform'),
            'device' => $request?->header('Sec-CH-UA-Mobile'),
            'user_agent' => $request?->userAgent(),
            'properties' => $properties,
            'logged_at' => now(),
        ]);
    }

    /**
     * Resolve a stable entity type slug from an Eloquent model.
     */
    private function entityType(Model $entity): string
    {
        return Str::snake(class_basename($entity));
    }
}
