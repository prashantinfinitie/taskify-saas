<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidate extends Model implements HasMedia
{
    use HasFactory;
    use Notifiable;
    use InteractsWithMedia;

    // public function registerMediaCollections(): void
    // {
    //     $media_storage_settings = get_settings('media_storage_settings');
    //     $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';
    //     if ($mediaStorageType === 's3') {
    //         $this->addMediaCollection('candidate-media')->useDisk('s3');
    //     } else {
    //         $this->addMediaCollection('candidate-media')->useDisk('public');
    //     }
    // }

    protected $fillable = ['id', 'workspace_id' ,'admin_id' , 'name', 'email', 'phone', 'position', 'source', 'status_id',];

    public function status()
    {
        return $this->belongsTo(CandidateStatus::class, 'status_id');
    }

    public function interviews(){
        return $this->hasMany(Interview::class);
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'candidate_notification')->where('notifications.workspace_id', getWorkspaceId())->withPivot('read_at', 'is_system', 'is_push');
    }

}
