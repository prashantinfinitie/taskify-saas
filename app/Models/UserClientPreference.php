<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClientPreference extends Model
{
    use HasFactory;

    protected $table = 'user_client_preferences';

    protected $fillable = [
        'user_id',
        'table_name',
        'visible_columns',
        'default_view',
    ];

    protected $casts = [
        'visible_columns' => 'array',
    ];
    public $timestamps = false;
}
