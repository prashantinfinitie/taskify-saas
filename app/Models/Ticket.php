<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'status',
        'priority_id',
        'category_id'
    ];

    /**
     * Get the admin who submitted the ticket.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }



    /**
     * Get the category of the ticket.
     */
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * Get the replies for the ticket.
     */
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }
    public function media()
    {
        return $this->hasMany(TicketMedia::class);
    }
}
