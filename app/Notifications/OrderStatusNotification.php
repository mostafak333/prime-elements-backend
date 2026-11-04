<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    public function __construct(
        private readonly int $orderId,
        private readonly string $orderReference,
        private readonly string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'title_key' => 'messages.notification.order_status_updated_title',
            'message_key' => 'messages.notification.order_status_updated_message',
            'order' => [
                'id' => $this->orderId,
                'reference' => $this->orderReference,
                'status' => $this->status,
            ],
        ];
    }
}
