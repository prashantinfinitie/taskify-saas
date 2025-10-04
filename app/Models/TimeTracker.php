<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTracker extends Model
{
    use HasFactory;
    protected $fillable = [
        'workspace_id',
        'user_id',
        'start_date_time',
        'end_date_time',
        'duration',
        'message',
        'project_id',
        'task_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()  // Change from users() to user()
    {
        return $this->belongsTo(User::class);  // Use belongsTo instead of belongsToMany
    }
}
