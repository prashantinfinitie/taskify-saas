<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMedia extends Model
{
    protected $fillable = ['ticket_id', 'media_path'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
