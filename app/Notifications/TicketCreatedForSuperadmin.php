<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class TicketCreatedForSuperadmin extends Notification
{
    protected $ticket;
    protected $admin;

    public function __construct(Ticket $ticket, $admin)
    {
        $this->ticket = $ticket;
        $this->admin = $admin;
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

        $subjectPlaceholders = [
            '{COMPANY_TITLE}' => $company_title,
            '{TICKET_TITLE}' => $this->ticket->title,
            '{ADMIN_NAME}' => $this->admin->first_name . ' ' . $this->admin->last_name,
        ];

        $subject = 'New Ticket Created - #' . $this->ticket->id . ' - ' . $company_title;

        $subject = str_replace(array_keys($subjectPlaceholders), array_values($subjectPlaceholders), $subject);

        $messagePlaceholders = [
            '{TICKET_TITLE}' => $this->ticket->title,
            '{TICKET_DESCRIPTION}' => $this->ticket->description,
            '{ADMIN_NAME}' => $this->admin->first_name . ' ' . $this->admin->last_name,
            '{COMPANY_TITLE}' => $company_title,
            '{SITE_URL}' => $siteUrl,
            '{CURRENT_YEAR}' => date('Y'),
            '{subject}' => $subject,
            '{TICKET_ID}' => $this->ticket->id,
        ];


        $defaultTemplatePath = resource_path('views/mail/default_templates/ticket_created_superadmin.blade.php');
        if (!File::exists($defaultTemplatePath)) {
            $defaultTemplatePath = resource_path('views/mail/default_templates/default.blade.php');
        }

        $defaultTemplateContent = File::get($defaultTemplatePath);
        $emailTemplate = str_replace(array_keys($messagePlaceholders), array_values($messagePlaceholders), $defaultTemplateContent);

        return (new MailMessage)
            ->view('mail.html', ['content' => $emailTemplate, 'logo_url' => asset($full_logo)])
            ->subject($subject);
    }
}
