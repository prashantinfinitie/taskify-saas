<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'priority',
        'description',
        'creator_id',
        'creator_type',
        'completed',
        'workspace_id',
        'admin_id',
    ];
    public function creator()
    {
        return $this->morphTo();
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

     protected static function boot()
    {
        parent::boot();

        // Cascade delete reminders when a Task is deleted
        static::deleting(function ($task) {
            // Delete all reminders related to this task
            $task->reminders()->delete();
        });
    }
    
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }
}
