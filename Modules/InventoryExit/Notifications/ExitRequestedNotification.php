<?php

namespace Modules\InventoryExit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\InventoryExit\Entities\InventoryExitRequest;

class ExitRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(private InventoryExitRequest $exitRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $requester   = $this->exitRequest->requestedBy?->name ?? '—';
        $costCenter  = $this->exitRequest->locationLabel();
        $reason      = $this->exitRequest->exitReason?->name ?? '—';
        $date        = $this->exitRequest->exit_date?->format('d/m/Y') ?? '—';

        return (new MailMessage)
            ->subject(__('inventoryexit::notifications.exit_requested_subject'))
            ->greeting(__('inventoryexit::notifications.greeting', ['name' => $notifiable->name]))
            ->line(__('inventoryexit::notifications.exit_requested_body', [
                'requester'   => $requester,
                'cost_center' => $costCenter,
                'reason'      => $reason,
                'date'        => $date,
            ]))
            ->action(
                __('inventoryexit::notifications.view_request'),
                route('inventory_exit.index')
            );
    }

    public function toArray($notifiable): array
    {
        return [
            'exit_request_id' => $this->exitRequest->id,
            'type'            => 'exit_requested',
        ];
    }
}
