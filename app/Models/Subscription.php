<?php

// app/Models/Subscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'plan_id',
        'user_id',
        'payment_method',
        'tenure',
        'charging_price',
        'charging_currency',
        'is_recurring',
        'recurring_each_days',
        'starts_at',
        'ends_at',
        'cancelled_on',
        'features',
    ];

    protected $casts = [

        'features' => 'json',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function bankTransferDocuments()
    {
        return $this->hasMany(BankTransferDocument::class);
    }
}
