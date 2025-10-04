<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    protected $fillable = [

        'frequency_type',
        'frequency_value',
        'day_of_week',
        'day_of_month',
        'time_of_day',
        'is_active',
        'last_sent_at',
    ];
    public function remindable()
    {
        return $this->morphTo();
    }
}
