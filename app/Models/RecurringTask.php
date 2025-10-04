<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTask extends Model
{
    use HasFactory;
    protected $fillable = ['task_id', 'frequency', 'day_of_week', 'day_of_month', 'month_of_year', 'starts_from', 'number_of_occurrences', 'completed_occurrences', 'is_active', 'last_created_at'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
