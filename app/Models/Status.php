<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'color',
        'slug',
        'admin_id'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class)->where('projects.workspace_id', session()->get('workspace_id'));
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->where('tasks.workspace_id', session()->get('workspace_id'));
    }

    public function user_tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->where('tasks.workspace_id', session()->get('workspace_id'));
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_status');
    }
}
