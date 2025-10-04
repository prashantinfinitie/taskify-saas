<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use App\Models\User;

class TicketStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $oldStatus;
    protected $newStatus;
    protected $updatedBy;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Ticket  $ticket
     * @param  string  $oldStatus
     * @param  string  $newStatus
     * @param  \App\Models\User  $updatedBy
     * @return void
     */
    public function __construct(Ticket $ticket, $oldStatus, $newStatus, User $updatedBy)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id} Status Updated")
            ->greeting('Hello,')
            ->line("The status of Ticket #{$this->ticket->id} has been updated.")
            ->line("**Old Status:** " . ucwords(str_replace('_', ' ', $this->oldStatus)))
            ->line("**New Status:** " . ucwords(str_replace('_', ' ', $this->newStatus)))
            ->line("**Updated by:** " . ucfirst($this->updatedBy->first_name) . ' ' . ucfirst($this->updatedBy->last_name))
            ->action('View Ticket', url("/support/{$this->ticket->id}"))
            ->line('Thank you for using our support system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'ticket_id'   => $this->ticket->id,
            'old_status'  => $this->oldStatus,
            'new_status'  => $this->newStatus,
            'updated_by'  => $this->updatedBy->id,
        ];
    }
}
