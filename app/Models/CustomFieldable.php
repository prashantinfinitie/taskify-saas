<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldable extends Model
{
    protected $table = 'custom_fieldables';

    protected $fillable = [
        'custom_field_id',
        'custom_fieldable_id',
        'custom_fieldable_type',
        'value'
    ];

    /**
     * Get the parent custom_fieldable model.
     */
    public function custom_fieldable()
    {
        return $this->morphTo();
    }

    /**
     * Get the custom field that owns this value.
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
