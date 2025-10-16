<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Model
{
    use HasFactory,HasRoles;

    protected static function booted()
    {
        static::deleting(function ($admin) {
            // 1️⃣ Get all team member user IDs
            $teamMemberUserIds = $admin->teamMembers()->pluck('user_id');
            dd($teamMemberUserIds);
            // 2️⃣ Delete users in one go
            \App\Models\User::whereIn('id', $teamMemberUserIds)->delete();

            // 3️⃣ Delete team member records themselves
            $admin->teamMembers()->delete();

            // 4️⃣ Delete admin's own user
            if ($admin->user) {
                $admin->user->delete();
            }
        });
    }


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
