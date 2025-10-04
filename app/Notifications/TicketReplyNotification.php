<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use App\Models\TicketReply;

class TicketReplyNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $reply;
    protected $senderName;

    public function __construct(Ticket $ticket, TicketReply $reply, $senderName)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
        $this->senderName = $senderName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $senderRole = ucfirst($this->reply->sender_role);

        return (new MailMessage)
            ->subject("New Reply to Ticket #{$this->ticket->id}")
            ->line("A new reply has been added to Ticket #{$this->ticket->id}.")
            ->line("Sender: {$this->senderName} ({$senderRole})")
            ->line("Reply: {$this->reply->message}")
            ->action('View Ticket', url("/support/{$this->ticket->id}"))
            ->line('Thank you for using our support system!');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'reply_id' => $this->reply->id,
            'sender_role' => $this->reply->sender_role,
            'sender_name' => $this->senderName,
        ];
    }
}
