<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class TicketCreatedForAdmin extends Notification
{
    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $general_settings = get_settings('general_settings');
        $full_logo = !isset($general_settings['full_logo']) || empty($general_settings['full_logo']) ? 'storage/logos/default_full_logo.png' : 'storage/' . $general_settings['full_logo'];
        $company_title = $general_settings['company_title'] ?? 'Taskify';
        $siteUrl = request()->getSchemeAndHttpHost();
        $subject = 'Ticket Successfully Submitted - #' . $this->ticket->id . ' - ' . $company_title;
        $messagePlaceholders = [
            '{TICKET_TITLE}' => $this->ticket->title,
            '{TICKET_DESCRIPTION}' => $this->ticket->description,
            '{COMPANY_TITLE}' => $company_title,
            '{SITE_URL}' => $siteUrl,
            '{CURRENT_YEAR}' => date('Y'),
            '{FIRST_NAME}' => $notifiable->first_name,
            '{LAST_NAME}' => $notifiable->last_name,
            '{subject}' =>  $subject,
            '{TICKET_ID}' => $this->ticket->id
        ];

        // Load template
        $defaultTemplatePath = resource_path('views/mail/default_templates/ticket_created_admin.blade.php');
        $defaultTemplateContent = File::get($defaultTemplatePath);

        // Replace placeholders
        $emailTemplate = str_replace(array_keys($messagePlaceholders), array_values($messagePlaceholders), $defaultTemplateContent);

        // Log to debug
        // dd($emailTemplate);

        return (new MailMessage)
            ->view('mail.html', ['content' => $emailTemplate, 'logo_url' => asset($full_logo)])
            ->subject($subject);
    }
}
