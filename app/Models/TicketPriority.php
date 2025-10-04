<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    /**
     * Get the tickets associated with the priority.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
