<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];
        $order = $data['order'] ?? [];
        $status = $order['status'] ?? null;
        $statusLabel = $status ? __('messages.notification.status.'.$status) : null;

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? $this->type,
            'title' => isset($data['title_key']) ? __($data['title_key']) : ($data['title'] ?? null),
            'message' => isset($data['message_key'])
                ? __($data['message_key'], [
                    'order_reference' => $order['reference'] ?? '',
                    'status' => $statusLabel ?? $status ?? '',
                ])
                : ($data['message'] ?? null),
            'data' => $data['data'] ?? Arr::except($data, ['title_key', 'message_key', 'type']),
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
