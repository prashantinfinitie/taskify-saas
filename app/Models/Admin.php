<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Model
{
    use HasFactory,HasRoles;
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }
}
