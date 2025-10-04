<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\RecurringTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'recurring-tasks:generate';
    protected $description = 'Generate new tasks for recurring tasks';

    private const FREQUENCY_HANDLERS = [
        'daily' => 'handleDailyFrequency',
        'weekly' => 'handleWeeklyFrequency',
        'monthly' => 'handleMonthlyFrequency',
        'yearly' => 'handleYearlyFrequency'
    ];

    private const DAYS_MAP = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    public function handle(): void
    {
        try {
            Log::info('Starting recurring task generation process');

            $recurringTasks = $this->getActiveRecurringTasks();

            $recurringTasks->each(function ($task) {
                $this->processRecurringTask($task);
            });

            $this->info('Recurring task generation completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in recurring task generation: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            $this->error('Failed to generate recurring tasks');
        }
    }

    protected function getActiveRecurringTasks(): Collection
    {
        return RecurringTask::with(['task.users'])
            ->where('is_active', 1)
            ->get();
    }

    protected function processRecurringTask(RecurringTask $recurringTask): void
    {
        try {
            if (!$this->validateRecurringTask($recurringTask)) {
                return;
            }

            $nextDate = $this->calculateNextOccurrence($recurringTask);

            if (!$this->shouldCreateNewTask($recurringTask, $nextDate)) {
                return;
            }

            $this->createNewTask($recurringTask, $nextDate);
            $this->updateRecurringTask($recurringTask, $nextDate);
        } catch (\Exception $e) {
            Log::error("Error processing recurring task {$recurringTask->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    protected function validateRecurringTask(RecurringTask $recurringTask): bool
    {
        if (!$recurringTask->task) {
            Log::warning("Parent task not found for recurring task {$recurringTask->id}");
            return false;
        }

        if ($this->hasReachedMaxOccurrences($recurringTask)) {
            Log::info("Max occurrences reached for recurring task {$recurringTask->id}");
            return false;
        }

        return true;
    }

    protected function calculateNextOccurrence(RecurringTask $recurringTask): Carbon
    {
        $timezone = config('app.timezone', 'UTC');
        $startDate = $recurringTask->starts_from
            ? Carbon::parse($recurringTask->starts_from)
            : now($timezone);

        $handler = self::FREQUENCY_HANDLERS[$recurringTask->frequency] ?? null;

        if (!$handler || !method_exists($this, $handler)) {
            throw new \InvalidArgumentException("Invalid frequency: {$recurringTask->frequency}");
        }

        return $this->{$handler}($recurringTask, $startDate);
    }

    protected function shouldCreateNewTask(RecurringTask $recurringTask, Carbon $nextDate): bool
    {
        if ($recurringTask->last_created_at) {
            $lastCreated = Carbon::parse($recurringTask->last_created_at);
            if ($lastCreated->gte($nextDate)) {
                Log::info("Task already created for date {$nextDate} for recurring task {$recurringTask->id}");
                return false;
            }
        }

        return true;
    }

    protected function createNewTask(RecurringTask $recurringTask, Carbon $nextDate): void
    {
        $parentTask = $recurringTask->task;
        $status = $this->getDefaultStatus();

        $newTask = $parentTask->replicate()
            ->fill([
                'due_date' => $nextDate,
                'status_id' => $status->id
            ]);

        $newTask->save();

        $this->createStatusTimeline($newTask, $status);
        $this->assignUsers($newTask, $parentTask->users);
        $this->sendNotification($newTask);
        Log::info("Created new task {$newTask->id} from recurring task {$recurringTask->id}");
    }

    protected function updateRecurringTask(RecurringTask $recurringTask, Carbon $nextDate): void
    {
        $recurringTask->update([
            'last_created_at' => $nextDate,
            'completed_occurrences' => ($recurringTask->completed_occurrences ?? 0) + 1
        ]);
    }

    private function handleDailyFrequency(RecurringTask $recurringTask, Carbon $date): Carbon
    {
        return $date->copy()->addDay();
    }

    private function handleWeeklyFrequency(RecurringTask $recurringTask, Carbon $date): Carbon
    {
        if (!$recurringTask->day_of_week) {
            return $date->copy()->addWeek();
        }

        $targetDay = self::DAYS_MAP[$recurringTask->day_of_week] ?? 'monday';
        $nextDate = $date->copy();

        return $nextDate->dayOfWeek !== $targetDay && $nextDate->modify($targetDay)->gt($date)
            ? $nextDate->modify($targetDay)
            : $nextDate->modify('next ' . $targetDay);
    }

    private function handleMonthlyFrequency(RecurringTask $recurringTask, Carbon $date): Carbon
    {
        $nextDate = $date->copy()->addMonth();

        if ($recurringTask->day_of_month) {
            $nextDate->setDay(min((int)$recurringTask->day_of_month, $nextDate->daysInMonth));
        }

        return $nextDate;
    }

    private function handleYearlyFrequency(RecurringTask $recurringTask, Carbon $date): Carbon
    {
        $nextDate = $date->copy()->addYear();

        if ($recurringTask->month_of_year && $recurringTask->day_of_month) {
            $nextDate->setMonth((int)$recurringTask->month_of_year)
                ->setDay(min((int)$recurringTask->day_of_month, $nextDate->daysInMonth));
        }

        return $nextDate;
    }

    private function hasReachedMaxOccurrences(RecurringTask $recurringTask): bool
    {
        return $recurringTask->number_of_occurrences &&
            ($recurringTask->completed_occurrences ?? 0) >= $recurringTask->number_of_occurrences;
    }

    private function createStatusTimeline(Task $task, $status): void
    {
        $task->statusTimelines()->create([
            'status' => $status->title,
            'new_color' => $status->color,
            'previous_status' => '-',
            'changed_at' => now(),
        ]);
    }

    private function assignUsers(Task $task, Collection $users): void
    {
        $task->users()->attach($users->pluck('id'));
    }

    private function getDefaultStatus()
    {
        return getDefaultStatus('Open');
    }

    private function sendNotification($task): void
    {
        Log::info('Recurring Task Detail' . json_encode($task, JSON_PRETTY_PRINT));
        // Send notifications to users
        $notificationData = [
            'type' => 'recurring_task', // or project_reminder based on your needs
            'type_id' => $task->id,
            'action' => 'recurring_task',
            'workspace_id' => $task->workspace_id,
            'type_title' => $task->title,
            'access_url' => 'tasks/information/' . $task->id,
            'from_id' => $task->admin_id,
        ];
        // Send notification to users using your preferred notification service
        $recepients =
            $task->users->map(function ($user) {
                return 'u_' . $user->id; // Format: 'u_1' for users, 'c_1' for clients
            })->toArray();
        Log::info('Sending Recurring Task Notification with Data: ' . json_encode($notificationData, JSON_PRETTY_PRINT) . ' to Recipients: ' . json_encode($recepients, JSON_PRETTY_PRINT));
        sendReminderNotification($notificationData, $recepients);
    }
}
