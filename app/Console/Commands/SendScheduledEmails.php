<?php

namespace App\Console\Commands;

use Throwable;
use Carbon\Carbon;
use App\Jobs\SendEmailJob;
use App\Models\ScheduledEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Notifications\DynamicTemplateMail;

class SendScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails that are scheduled and due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now('UTC');

        $this->info("Current time (UTC): {$now->toDateTimeString()}");
        $this->info("Fetching scheduled emails that are due...");

        $scheduledEmails = ScheduledEmail::where('status', 'pending')
            ->where('scheduled_at', '<=', $now)
            ->get();

        $totalEmails = $scheduledEmails->count();
        $this->info("Found {$totalEmails} scheduled emails to process.");

        if ($totalEmails === 0) {
            $this->info("No scheduled emails to send at this time.");
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($scheduledEmails as $email) {
            try {
                Log::info("Processing scheduled email ID: {$email->id}");

                $mailable = new DynamicTemplateMail($email);

                // Attach files if they exist (if attachments are stored properly)
                if (!empty($email->attachments) && is_array($email->attachments)) {
                    foreach ($email->attachments as $attachment) {
                        try {
                            $mailable->attachFromStorageDisk(
                                'public',
                                $attachment['path'],
                                $attachment['original_name'] ?? basename($attachment['path']),
                                ['mime' => $attachment['mime_type'] ?? 'application/octet-stream']
                            );
                        } catch (Throwable $attachEx) {
                            Log::warning("Attachment failed for email ID {$email->id}: " . $attachEx->getMessage());
                        }
                    }
                }

                Mail::to($email->to_email)->send($mailable);

                $email->update(['status' => 'sent']);
                Log::info("Email ID {$email->id} sent successfully.");
                $sentCount++;
            } catch (Throwable $th) {
                Log::error("Failed to send scheduled email ID {$email->id}: " . $th->getMessage(), [
                    'exception' => $th,
                ]);

                $email->update(['status' => 'failed']);
                $failedCount++;
                continue; // Continue with next email instead of stopping
            }
        }

        $this->info("Scheduled emails processing complete.");
        $this->info("Total: {$totalEmails} | Sent: {$sentCount} | Failed: {$failedCount}");

        return Command::SUCCESS;
    }
}
