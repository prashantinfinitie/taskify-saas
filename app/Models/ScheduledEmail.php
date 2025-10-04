<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class ScheduledEmail extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $media_storage_settings = get_settings('media_storage_settings');
        $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';
        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('email-media')->useDisk('s3');
        } else {
            $this->addMediaCollection('email-media')->useDisk('public');
        }
    }

    protected $fillable = ['email_template_id', 'to_email', 'subject', 'body', 'placeholders', 'scheduled_at', 'status', 'attachments','user_id','workspace_id'];

    protected $casts = [
        'placeholders' => 'array',
        'scheduled_at' => 'datetime',
        'attachments' => 'array',
    ];
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
