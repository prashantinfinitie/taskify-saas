<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'created_by',
        'title',
        'content',
        'priority',
        'start_date',
        'end_date',
        'all_workspace_users',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('is_read', 'read_at');
    }

    public function toFullCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start_date->format('Y-m-d'), // ISO 8601 format
            'end' => $this->end_date->addDay()->format('Y-m-d'), // Add a day to include full end date
            'backgroundColor' => $this->getPriorityClass() ?? '#6ed4f0', // Default to bg-info if null
            'textColor' => '#000000',
            'borderColor' => '#000000', // Optional for consistency
            'created_by' => $this->created_by,
            'extendedProps' => [
                'content' => $this->content,
                'priority' => ucwords($this->priority),
                'createdBy' => $this->creator ? ucwords($this->creator->first_name . ' ' . $this->creator->last_name) : 'Unknown', // Handle null creator
            ],
        ];
    }


    // Helper method to get CSS class based on priority
    public function getPriorityClass()
    {
        return match ($this->priority) {
            'high' => '#ff6b5c',
            'medium' => '#ffca66',
            'low' => '#a0e4a3',
            default => '#6ed4f0'
        };
    }
}
