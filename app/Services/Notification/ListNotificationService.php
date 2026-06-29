<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Paginated notification listing for authenticated users.
 */
class ListNotificationService
{
    /**
     * @param  array{unread_only?: bool, per_page?: int}  $filters
     */
    public function handle(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->notifications()->latest();

        if ($filters['unread_only'] ?? false) {
            $query->whereNull('read_at');
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
