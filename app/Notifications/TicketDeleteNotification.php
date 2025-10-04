<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketDeleteNotification extends Notification
{
    use Queueable;
    protected $ticketDetails;
    /**
     * Create a new notification instance.
     */
    public function __construct($ticketDetails)
    {
        $this->ticketDetails = $ticketDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticketDetails['id']} Has Been Deleted")
            ->greeting("Hello!")
            ->line("This is to inform you that Ticket #{$this->ticketDetails['id']} has been deleted by a Superadmin.")
            ->line('Ticket Details:')
            ->line('Title: ' . $this->ticketDetails['title'])
            ->line('Priority: ' . $this->ticketDetails['priority'])
            ->line('Status: ' . $this->ticketDetails['status'])
            ->line('Description: ' . $this->ticketDetails['description'])
            ->action('View Tickets', url('/support'))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_title' => $this->ticketDetails['title'],
            'admin' => $this->ticketDetails['admin'],
            'priority' => $this->ticketDetails['priority'],
            'description' => $this->ticketDetails['description'],
            'status' => $this->ticketDetails['status'],
            'user_id' => $this->ticketDetails['user_id'],
        ];
    }
}
