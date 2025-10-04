<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeEntry extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_id',
        'entry_date',
        'is_billable',
        'entry_type',
        'standard_hours',
        'start_time',
        'end_time',
        'description',
        'workspace_id',
        'user_id',
    ];
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function user_type()
    {
        $type = str_starts_with($this->user_id, 'u_') ? 'user' : (str_starts_with($this->user_id, 'c_') ? 'client' : null);

        return [
            'type' => $type,
            'id' => str_replace(['u_', 'c_'], '', $this->user_id)
        ];
    }
}
