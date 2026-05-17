<?php

namespace Modules\InventoryExit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\InventoryExit\Entities\InventoryExitRequest;

class ExitStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private InventoryExitRequest $exitRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = match($this->exitRequest->status) {
            'approved' => __('inventoryexit::messages.status_approved'),
            'rejected' => __('inventoryexit::messages.status_rejected'),
            default    => $this->exitRequest->status,
        };

        $approver   = $this->exitRequest->approvedBy?->name ?? '—';
        $note       = $this->exitRequest->approval_note ?? '—';
        $costCenter = $this->exitRequest->locationLabel();

        return (new MailMessage)
            ->subject(__('inventoryexit::notifications.exit_status_changed_subject', ['status' => $statusLabel]))
            ->greeting(__('inventoryexit::notifications.greeting', ['name' => $notifiable->name]))
            ->line(__('inventoryexit::notifications.exit_status_body', [
                'status'      => $statusLabel,
                'cost_center' => $costCenter,
                'approver'    => $approver,
                'note'        => $note,
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
            'type'            => 'exit_status_changed',
            'status'          => $this->exitRequest->status,
        ];
    }
}
