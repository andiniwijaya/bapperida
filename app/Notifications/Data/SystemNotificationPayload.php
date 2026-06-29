<?php

namespace App\Notifications\Data;

/**
 * Structured payload for system notifications across database, mail, and future broadcast channels.
 *
 * Communication layer: carries user-facing title, message, and module context for the notification API.
 */
readonly class SystemNotificationPayload
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<int, string>  $channels  Laravel notification channels (database, mail, broadcast).
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $module,
        public string $action,
        public ?string $url = null,
        public array $metadata = [],
        public array $channels = ['database'],
    ) {}
}
