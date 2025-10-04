<?php

namespace Plugins\AssetManagement\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'action',
        'lent_to',
        'date_given',
        'estimated_return_date',
        'actual_return_date',
        'returned_by',
        'notes'
    ];

    protected $casts = [
        'date_given' => 'datetime',
        'estimated_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lentToUser()
    {
        return $this->belongsTo(User::class, 'lent_to');
    }

    public function returnedByUser()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
