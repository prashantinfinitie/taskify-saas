<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Client;
use App\Models\Template;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class AssignmentNotification extends VerifyEmailBase
{
    protected $recipient;
    protected $data;
    protected $general_settings;

    public function __construct($recipient, $data)
    {
        $this->recipient = $recipient;
        $this->data = $data;
        $this->general_settings = get_settings('general_settings');
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $full_logo_path = !isset($this->general_settings['full_logo']) || empty($this->general_settings['full_logo']) ? 'logos/default_full_logo.png' : $this->general_settings['full_logo'];
        $full_logo_url = asset('storage/' . $full_logo_path);
        $subject = $this->getSubject();
        $content = $this->getContent();

        return (new MailMessage)
            ->view('mail.html', ['content' => $content, 'logo_url' => $full_logo_url])
            ->subject($subject);
    }


    protected function getSubject()
    {
        $company_title = $this->general_settings['company_title'] ?? 'Taskify';
        $fetched_data = Template::where('type', 'email')
            ->where('name', $this->data['type'] . '_assignment')
            ->first();

        if (!$fetched_data) {
            // If template with $this->data['type'] . '_assignment' name not found, check for template with $this->data['type'] name
            $fetched_data = Template::where('type', 'email')
                ->where('name', $this->data['type'])
                ->first();
        }


        $subject = 'Default Subject'; // Set a default subject
        $subjectPlaceholders = [];

        // Customize subject based on type
        switch ($this->data['type']) {
            case 'project':
                $subjectPlaceholders = [
                    '{PROJECT_ID}' => $this->data['type_id'],
                    '{PROJECT_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'task':
                $subjectPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'workspace':
                $subjectPlaceholders = [
                    '{WORKSPACE_ID}' => $this->data['type_id'],
                    '{WORKSPACE_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'meeting':
                $subjectPlaceholders = [
                    '{MEETING_ID}' => $this->data['type_id'],
                    '{MEETING_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'leave_request_creation':
                $subjectPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{STATUS}' => $this->data['status'],
                    '{REQUESTEE_FIRST_NAME}' => $this->data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $this->data['team_member_last_name'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'team_member_on_leave_alert':
                $subjectPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{REQUESTEE_FIRST_NAME}' => $this->data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $this->data['team_member_last_name'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'leave_request_status_updation':
                $subjectPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'project_status_updation':
                $subjectPlaceholders = [
                    '{PROJECT_ID}' => $this->data['type_id'],
                    '{PROJECT_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'project_issue':
                $subjectPlaceholders = [
                    '{ISSUE_ID}' => $this->data['type_id'],
                    '{ISSUE_TITLE}' => $this->data['type_title'],
                    '{STATUS}' => $this->data['status'],
                    '{CREATOR_FIRST_NAME}' => $this->data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $this->data['creator_last_name'],
                    '{ACCESS_URL}' => $this->data['access_url'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'task_status_updation':
                $subjectPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'announcement':
                $subjectPlaceholders = [
                    '{ANNOUNCEMENT_ID}' => $this->data['type_id'],
                    '{ANNOUNCEMENT_TITLE}' => $this->data['type_title'],
                    '{CREATOR_FIRST_NAME}' => $this->data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $this->data['creator_last_name'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{CURRENT_YEAR}' => date('Y'),
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                ];
                break;
            case 'task_reminder':
            case 'recurring_task':
                $subjectPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{COMPANY_TITLE}' => $company_title,

                ];
                break;

            case 'todo_reminder':
                $subjectPlaceholders = [
                '{TODO_ID}' => $this->data['type_id'],
                '{TODO_TITLE}' => $this->data['type_title'],
                '{COMPANY_TITLE}' => $company_title,
            ];
            break;

             case 'interview_assignment':
                $subjectPlaceholders = [
                    '{INTERVIEW_ID}' => $this->data['type_id'],
                    '{CANDIDATE_NAME}' => $this->data['candidate_name'],
                    '{ROUND}' => $this->data['round'],
                    '{SCHEDULED_AT}' => $this->data['scheduled_at'],
                    '{INTERVIEWER_FIRST_NAME}' => $this->data['interviewer_first_name'],
                    '{INTERVIEWER_LAST_NAME}' => $this->data['interviewer_last_name'],
                    '{FULL_NAME}' =>  $this->recipient->name,
                    '{ASSIGNEE_FIRST_NAME}' => $this->recipient->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;
            case 'interview_status_update':
                $subjectPlaceholders = [
                    '{INTERVIEW_ID}' => $this->data['type_id'],
                    '{CANDIDATE_NAME}' => $this->data['candidate_name'],
                    '{ROUND}' => $this->data['round'],
                    '{SCHEDULED_AT}' => $this->data['scheduled_at'],
                    '{INTERVIEWER_FIRST_NAME}' => $this->data['interviewer_first_name'],
                    '{INTERVIEWER_LAST_NAME}' => $this->data['interviewer_last_name'],
                    '{FULL_NAME}' =>  $this->recipient->name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;    

        }
        if (filled(Arr::get($fetched_data, 'subject'))) {
            $subject = $fetched_data->subject;
        } else {
            if ($this->data['type'] == 'leave_request_creation') {
                $subject = 'Leave Requested - {COMPANY_TITLE}';
            } elseif ($this->data['type'] == 'leave_request_status_updation') {
                $subject = 'Leave Request Status Updated - {COMPANY_TITLE}';
            } elseif ($this->data['type'] == 'team_member_on_leave_alert') {
                $subject = 'Team Member On Leave Alert - {COMPANY_TITLE}';
            } elseif ($this->data['type'] == 'project_status_updation') {
                $subject = 'Project Status Updated - {COMPANY_TITLE}';
            } elseif ($this->data['type'] == 'task_status_updation') {
                $subject = 'Task Status Updated - {COMPANY_TITLE}';
            } else {
                $subject = 'New ' . ucfirst($this->data['type']) . ' Assignment - {COMPANY_TITLE}';
            }
        }

        $subject = str_replace(array_keys($subjectPlaceholders), array_values($subjectPlaceholders), $subject);

        return $subject;
    }


    protected function getContent()
    {
        $company_title = $this->general_settings['company_title'] ?? 'Taskify';
        $siteUrl = request()->getSchemeAndHttpHost() . '/master-panel';

        $fetched_data = Template::where('type', 'email')
            ->where('name', $this->data['type'] . '_assignment')
            ->first();

        if (!$fetched_data) {
            // If template with $this->data['type'] . '_assignment' name not found, check for template with $this->data['type'] name
            $fetched_data = Template::where('type', 'email')
                ->where('name', $this->data['type'])
                ->first();
        }


        $templateContent = 'Default Content';
        $contentPlaceholders = []; // Initialize outside the switch

        // Customize content based on type
        switch ($this->data['type']) {
            case 'project':
                $contentPlaceholders = [
                    '{PROJECT_ID}' => $this->data['type_id'],
                    '{PROJECT_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title,
                    '{PROJECT_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'task':
                $contentPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title,
                    '{TASK_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'workspace':
                $contentPlaceholders = [
                    '{WORKSPACE_ID}' => $this->data['type_id'],
                    '{WORKSPACE_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title,
                    '{WORKSPACE_URL}' => $siteUrl . '/workspaces',
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'meeting':
                $contentPlaceholders = [
                    '{MEETING_ID}' => $this->data['type_id'],
                    '{MEETING_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title,
                    '{MEETING_URL}' => $siteUrl . '/meetings',
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'leave_request_creation':
                $contentPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{USER_FIRST_NAME}' => $this->recipient->first_name,
                    '{USER_LAST_NAME}' => $this->recipient->last_name,
                    '{REQUESTEE_FIRST_NAME}' => $this->data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $this->data['team_member_last_name'],
                    '{TYPE}' => $this->data['leave_type'],
                    '{FROM}' => $this->data['from'],
                    '{TO}' => $this->data['to'],
                    '{DURATION}' => $this->data['duration'],
                    '{REASON}' => $this->data['reason'],
                    '{STATUS}' => $this->data['status'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;

            case 'leave_request_status_updation':
                $contentPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{USER_FIRST_NAME}' => $this->recipient->first_name,
                    '{USER_LAST_NAME}' => $this->recipient->last_name,
                    '{REQUESTEE_FIRST_NAME}' => $this->data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $this->data['team_member_last_name'],
                    '{TYPE}' => $this->data['leave_type'],
                    '{FROM}' => $this->data['from'],
                    '{TO}' => $this->data['to'],
                    '{DURATION}' => $this->data['duration'],
                    '{REASON}' => $this->data['reason'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;

            case 'team_member_on_leave_alert':
                $contentPlaceholders = [
                    '{ID}' => $this->data['type_id'],
                    '{USER_FIRST_NAME}' => $this->recipient->first_name,
                    '{USER_LAST_NAME}' => $this->recipient->last_name,
                    '{REQUESTEE_FIRST_NAME}' => $this->data['team_member_first_name'],
                    '{REQUESTEE_LAST_NAME}' => $this->data['team_member_last_name'],
                    '{TYPE}' => $this->data['leave_type'],
                    '{FROM}' => $this->data['from'],
                    '{TO}' => $this->data['to'],
                    '{DURATION}' => $this->data['duration'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;

            case 'project_status_updation':
                $contentPlaceholders = [
                    '{PROJECT_ID}' => $this->data['type_id'],
                    '{PROJECT_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{PROJECT_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'project_issue':
                $contentPlaceholders = [
                    '{ISSUE_ID}' => $this->data['type_id'],
                    '{ISSUE_TITLE}' => $this->data['type_title'],
                    '{STATUS}' => $this->data['status'],
                    '{CREATOR_FIRST_NAME}' => $this->data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $this->data['creator_last_name'],
                    '{ACCESS_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title
                ];
                break;

            case 'task_status_updation':
                $contentPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{TASK_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'announcement':
                $contentPlaceholders = [
                    '{ANNOUNCEMENT_ID}' => $this->data['type_id'],
                    '{FIRST_NAME}' => $this->recipient->first_name,
                    '{LAST_NAME}' => $this->recipient->last_name,
                    '{ANNOUNCEMENT_TITLE}' => $this->data['type_title'],
                    '{CREATOR_FIRST_NAME}' => $this->data['creator_first_name'],
                    '{CREATOR_LAST_NAME}' => $this->data['creator_last_name'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{ACCESS_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{CURRENT_YEAR}' => date('Y'),
                ];

                break;
            case 'task_reminder':
            case 'recurring_task':
                $contentPlaceholders = [
                    '{TASK_ID}' => $this->data['type_id'],
                    '{TASK_TITLE}' => $this->data['type_title'],
                    '{TASK_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl
                ];
                break;

                case 'todo_reminder':
                $contentPlaceholders = [
                    '{TODO_ID}' => $this->data['type_id'],
                    '{TODO_TITLE}' => $this->data['type_title'],
                    '{TODO_URL}' => $siteUrl . '/' . $this->data['access_url'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{SITE_URL}' => $siteUrl
                ];
                break;
                
                case 'interview_assignment':
                $contentPlaceholders = [
                    '{INTERVIEW_ID}' => $this->data['type_id'],
                    '{CANDIDATE_NAME}' => $this->data['candidate_name'],
                    '{ROUND}' => $this->data['round'],
                    '{SCHEDULED_AT}' => $this->data['scheduled_at'],
                    '{MODE}' => $this->data['mode'],
                    '{LOCATION}' => $this->data['location'] ?? 'N/A',
                    '{INTERVIEWER_FIRST_NAME}' => $this->data['interviewer_first_name'],
                    '{INTERVIEWER_LAST_NAME}' => $this->data['interviewer_last_name'],
                    '{FULL_NAME}' =>  $this->recipient->name,
                    '{ASSIGNEE_FIRST_NAME}' => $this->recipient->first_name,
                    '{ASSIGNEE_LAST_NAME}' => $this->recipient->last_name,
                    '{COMPANY_TITLE}' => $company_title,
                    '{INTERVIEW_URL}' => $siteUrl . '/interviews',
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
            case 'interview_status_update':
                $contentPlaceholders = [
                    '{INTERVIEW_ID}' => $this->data['type_id'],
                    '{CANDIDATE_NAME}' => $this->data['candidate_name'],
                    '{ROUND}' => $this->data['round'],
                    '{SCHEDULED_AT}' => $this->data['scheduled_at'],
                    '{MODE}' => $this->data['mode'],
                    '{LOCATION}' => $this->data['location'] ?? 'N/A',
                    '{INTERVIEWER_FIRST_NAME}' => $this->data['interviewer_first_name'],
                    '{INTERVIEWER_LAST_NAME}' => $this->data['interviewer_last_name'],
                    '{FULL_NAME}' =>  $this->recipient->name,
                    '{UPDATER_FIRST_NAME}' => $this->data['updater_first_name'],
                    '{UPDATER_LAST_NAME}' => $this->data['updater_last_name'],
                    '{OLD_STATUS}' => $this->data['old_status'],
                    '{NEW_STATUS}' => $this->data['new_status'],
                    '{COMPANY_TITLE}' => $company_title,
                    '{INTERVIEW_URL}' => $siteUrl . '/interviews',
                    '{SITE_URL}' => $siteUrl,
                    '{CURRENT_YEAR}' => date('Y')
                ];
                break;
        }
        if (filled(Arr::get($fetched_data, 'content'))) {
            $templateContent = $fetched_data->content;
        } else {
            if ($this->data['type'] === 'leave_request_creation' || $this->data['type'] === 'leave_request_status_updation' || $this->data['type'] === 'team_member_on_leave_alert' || $this->data['type'] === 'project_status_updation' || $this->data['type'] === 'task_status_updation') {
                $defaultTemplatePath = resource_path('views/mail/default_templates/' . $this->data['type'] . '.blade.php');
            } else {
                $defaultTemplatePath = resource_path('views/mail/default_templates/' . $this->data['type'] . '_assignment' . '.blade.php');
            }
            $defaultTemplateContent = File::get($defaultTemplatePath);
            $templateContent = $defaultTemplateContent;
        }

        // Replace placeholders with actual values
        $content = str_replace(array_keys($contentPlaceholders), array_values($contentPlaceholders), $templateContent);

        return $content;
    }
}
