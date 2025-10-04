<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','subscription_id', 'amount', 'currency', 'status','payment_method','transaction_id'];

    public function user()
    {
        return $this->belongsTo(User::class); // Assuming you have a User model
    }
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
