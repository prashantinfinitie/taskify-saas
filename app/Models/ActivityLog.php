<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'workspace_id',
        'actor_id',
        'actor_type',
        'type_id',
        'type',
        'parent_type_id',
        'parent_type',
        'activity',
        'message',
        'admin_id'
    ];

    public function getActorAttribute()
    {
        if ($this->actor_type === 'user') {
            return User::find($this->actor_id);
        } elseif ($this->actor_type === 'client') {
            return Client::find($this->actor_id);
        }
        return null;
    }
}
