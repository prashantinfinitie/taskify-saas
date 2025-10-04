<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'field_label',
        'module',
        'guide_text',
        'field_type',
        'options',
        'required',
        'visibility',
    ];

    public function fieldValues()
    {
        return $this->hasMany(CustomFieldable::class);
    }

}
