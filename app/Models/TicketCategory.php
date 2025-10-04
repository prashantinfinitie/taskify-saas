<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Ticket;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the tickets associated with the category.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
