<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use RyanChandler\Comments\Concerns\HasComments;
use App\Models\Favorite;
class Project extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;
    use HasComments;

    protected $fillable = [
        'title',
        'status_id',
        'budget',
        'start_date',
        'end_date',
        'description',
        'user_id',
        'client_id',
        'workspace_id',
        'admin_id',
        'created_by',
        'priority_id',
        'note',
        'task_accessibility',
        'enable_tasks_time_entries'
    ];

    public function registerMediaCollections(): void
    {
        $media_storage_settings = get_settings('media_storage_settings');
        $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';
        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('project-media')->useDisk('s3');
        } else {
            $this->addMediaCollection('project-media')->useDisk('public');
        }
    }

    public function scopeFilter($query, array $filters)
    {
        if ($filters['search_projects'] ?? false) {
            $query->where('title', 'like', '%' . request('search_projects') . '%')
                ->orWhere('status', 'like', '%' . request('search_projects') . '%');
        }
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class)->where('tasks.workspace_id', session()->get('workspace_id'));
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function getresult()
    {

        return substr($this->title, 0, 100);
    }

    public function getlink()
    {
        return str(route('projects.info', ['id' => $this->id]));
    }
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
 public function milestones()
{
    // Get workspace_id from header if API request, fallback to session if it's a web request
    $workspaceId = request()->header('workspace_id') ?? session()->get('workspace_id');

    return $this->hasMany(Milestone::class)
                ->where('milestones.workspace_id', $workspaceId);
}

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }


    /**
     * Relationship with Issues.
     */
    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
    public function statusTimelines()
    {
        return $this->morphMany(StatusTimeline::class, 'entity');
    }
    public function taskLists()
    {
        return $this->hasMany(TaskList::class, 'project_id');
    }

    public function customFieldValues()
    {
        return $this->morphMany('App\Models\CustomFieldable', 'custom_fieldable');
    }

    public function getCustomFieldValue($fieldId)
    {
        $customFieldValue = $this->customFieldValues()->where('custom_field_id', $fieldId)->first();
        return $customFieldValue ? $customFieldValue->value : null;
    }
}
