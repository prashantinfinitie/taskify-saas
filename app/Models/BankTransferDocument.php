<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransferDocument extends Model
{
    use HasFactory;
    protected $fillable = ['subscription_id', 'document_path'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
