<?php

namespace App\Notifications;

use App\Notifications\Data\SystemNotificationPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Generic Laravel notification for application communication events.
 *
 * Communication layer: database payload includes title, message, module, action, url, metadata.
 * Broadcast channel is registered for future Reverb/Pusher without sending realtime today.
 */
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SystemNotificationPayload $payload) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->payload->channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->payload->title,
            'message' => $this->payload->message,
            'module' => $this->payload->module,
            'action' => $this->payload->action,
            'url' => $this->payload->url,
            'metadata' => $this->payload->metadata,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->payload->title)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line($this->payload->message);

        if ($this->payload->url) {
            $mail->action('Buka Aplikasi', $this->payload->url);
        }

        return $mail->line('Terima kasih.');
    }

    /**
     * Future-ready broadcast payload for Laravel Reverb / Pusher.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
