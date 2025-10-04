<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily, weekly, and monthly task reminders to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Fetch system timezone from settings
            $systemTimezone = get_settings('general_settings')['timezone'] ?? 'UTC';
            Log::info('System Timezone: ' . $systemTimezone);

            // Use system timezone to get current time
            $now = Carbon::now($systemTimezone);
            Log::info('Executing Reminder command at: ' . $now->toDateTimeString());

            // Fetch active reminders that are due now and haven't been sent recently
            $reminders = $this->getDueReminders($now);

            Log::info('Found ' . $reminders->count() . ' reminders to process');

            foreach ($reminders as $reminder) {
                try {
                    $this->processReminder($reminder, $now, $systemTimezone);
                } catch (\Exception $e) {
                    Log::error('Failed to process reminder ID: ' . $reminder->id . '. Error: ' . $e->getMessage());
                    continue; // Continue with next reminder even if one fails
                }
            }

            Log::info('Reminder processing completed successfully');
            $this->info('Reminders sent successfully');

            return 0;
        } catch (\Exception $e) {
            Log::error('Reminder command failed: ' . $e->getMessage());
            $this->error('Reminder command failed: ' . $e->getMessage());

            return 1;
        }
    }

    private function getDueReminders(Carbon $now)
    {
        return Reminder::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    // Daily reminders
                    $q->where('frequency_type', 'daily')
                        ->whereTime('time_of_day', '<=', $now->toTimeString());
                })
                    ->orWhere(function ($q) use ($now) {
                        // Weekly reminders
                        $q->where('frequency_type', 'weekly')
                            ->where('day_of_week', $now->dayOfWeek)
                            ->whereTime('time_of_day', '<=', $now->toTimeString());
                    })
                    ->orWhere(function ($q) use ($now) {
                        // Monthly reminders
                        $q->where('frequency_type', 'monthly')
                            ->where('day_of_month', $now->day)
                            ->whereTime('time_of_day', '<=', $now->toTimeString());
                    });
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('last_sent_at')
                    ->orWhere(function ($q) use ($now) {
                        // Different intervals based on frequency type
                        $q->where(function ($sq) use ($now) {
                            $sq->where('frequency_type', 'daily')
                                ->where('last_sent_at', '<', $now->copy()->startOfDay());
                        })
                            ->orWhere(function ($sq) use ($now) {
                                $sq->where('frequency_type', 'weekly')
                                    ->where('last_sent_at', '<', $now->copy()->subWeek());
                            })
                            ->orWhere(function ($sq) use ($now) {
                                $sq->where('frequency_type', 'monthly')
                                    ->where('last_sent_at', '<', $now->copy()->subMonth());
                            });
                    });
            })
            ->get();
    }

    private function processReminder(Reminder $reminder, Carbon $now, string $systemTimezone)
    {
        Log::info('Processing reminder ID: ' . $reminder->id . ' Type: ' . $reminder->frequency_type);
        $remindable = $reminder->remindable;

        if (!$remindable) {
            Log::warning('Remindable not found for reminder ID: ' . $reminder->id);
            return;
        }

        if (!method_exists($remindable, 'users')) {
            Log::warning('Remindable does not have users relation. Reminder ID: ' . $reminder->id);
            return;
        }

        // Prepare notification data
        $notificationData = $this->prepareNotificationData($reminder, $remindable);

        // Get recipients (users associated with the remindable)
        $recipients = $this->getRecipients($remindable->users);

        // Send notifications using the notification function
        sendReminderNotification($notificationData, $recipients);

        // Update last sent timestamp
        $reminder->last_sent_at = Carbon::now($systemTimezone);
        $reminder->save();
    }

    private function prepareNotificationData(Reminder $reminder, $remindable)
    {
        switch ($reminder->remindable_type) {
            case 'App\Models\Task':
                return [
                    'type' => 'task_reminder', // or project_reminder based on your needs
                    'type_id' => $remindable->id,
                    'action' => 'task_reminder',
                    'workspace_id' => $remindable->workspace_id,
                    'type_title' => $remindable->title,
                    'access_url' => 'tasks/information/' . $remindable->id,
                    'from_id' => $remindable->admin_id,
                    // Add any other data needed for notifications
                ];
            case 'App\Models\Project':
                return [];

            case 'App\Models\Todo':
                return [
                    'type' => 'todo_reminder',
                    'type_id' => $remindable->id,
                    'action' => 'todo_reminder',
                    'workspace_id' => $remindable->workspace_id,
                    'type_title' => $remindable->title,
                    'access_url' => 'todos/',
                    'from_id' => $remindable->creator_id,
                ];

            default:
                // Handle other types of reminders here
                return [];
        }
    }

    private function getRecipients($users)
    {
        // If it's a single User model, wrap it in a collection
        if ($users instanceof \App\Models\User) {
            $users = collect([$users]);
        }

        // If it's null, return empty array
        if (is_null($users)) {
            return [];
        }

        return $users->map(function ($user) {
            return 'u_' . $user->id;
        })->toArray();
    }
}
