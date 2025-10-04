<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\ScheduledEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    // public $subject;
    // protected $general_settings;

    /**
     * Create a new message instance.
     */
    public function __construct(ScheduledEmail $email)
    {
        $this->email = $email;

    }

    public function build()
    {
        Log::info('Starting email build process');

        // Create the mail object
        $mail = $this->subject($this->email->subject)
            ->view('emails.template')
            ->with([
                'body' => $this->email->body,
                'to' => $this->email->to_email,
            ])->to($this->email->to_email);

        Log::info('Email base created with subject: ' . $this->email->subject);
        Log::info('Sending to: ' . $this->email->to_email);

        // Get attachments from media collection
        $attachments = $this->email->getMedia('email-media');
        Log::info('Found ' . $attachments->count() . ' attachment(s)');

        if ($attachments->isNotEmpty()) {
            foreach ($attachments as $index => $attachment) {
                // Log attachment details
                Log::info("Processing attachment #{$index}: {$attachment->file_name}");
                Log::info("- Original name: {$attachment->name}");
                Log::info("- MIME type: {$attachment->mime_type}");

                // Get the full path and check if file exists
                $filePath = $attachment->getPath();
                Log::info("- Full path: {$filePath}");
                Log::info("- File exists: " . (file_exists($filePath) ? 'Yes' : 'No'));

                // Get disk information
                $disk = $attachment->disk;
                Log::info("- Stored on disk: {$disk}");

                if (file_exists($filePath)) {
                    // Method 1: Attach directly using the path
                    $mail->attach($filePath, [
                        'as' => $attachment->file_name,
                        'mime' => $attachment->mime_type ?? 'application/octet-stream'
                    ]);
                    Log::info("- Attached using direct path method");
                } else {
                    // Method 2: Try using storage path if direct path fails
                    Log::warning("- Direct path failed, trying alternative method");

                    try {
                        // Try using the storage disk
                        if (Storage::disk($disk)->exists($attachment->id . '/' . $attachment->file_name)) {
                            $storagePath = $attachment->id . '/' . $attachment->file_name;
                            Log::info("- Storage path exists: {$storagePath}");

                            $mail->attachFromStorageDisk(
                                $disk,
                                $storagePath,
                                $attachment->file_name,
                                ['mime' => $attachment->mime_type ?? 'application/octet-stream']
                            );
                            Log::info("- Attached using storage disk method");
                        } else {
                            // Try another common path pattern with Spatie
                            $alternatePath = $attachment->collection_name . '/' . $attachment->id . '/' . $attachment->file_name;
                            Log::info("- Trying alternate path: {$alternatePath}");

                            if (Storage::disk($disk)->exists($alternatePath)) {
                                $mail->attachFromStorageDisk(
                                    $disk,
                                    $alternatePath,
                                    $attachment->file_name,
                                    ['mime' => $attachment->mime_type ?? 'application/octet-stream']
                                );
                                Log::info("- Attached using alternate path");
                            } else {
                                Log::error("- Failed to find attachment file using any method");
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("- Exception while attaching file: " . $e->getMessage());
                    }
                }
            }
        } else {
            Log::info('No attachments found for this email');
        }

        Log::info('Email build process completed');

        // Uncomment this line for one-time debugging
        // Log::info('Mail object: ' . print_r($mail, true));

        return $mail;
    }
}
