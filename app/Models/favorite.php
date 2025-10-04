<?php

// App\Models\Favorite.php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'favoritable_type',
        'favoritable_id'
    ];

    public function favoritable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
