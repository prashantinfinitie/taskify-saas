<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'is_required',
        'is_mapped',
        'options',
        'placeholder',
        'order',
        'validation_rules',
        'workspace_id',
        'admin_id'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_mapped' => 'boolean',
        'options' => 'array'
    ];

    const FIELD_TYPES = [
        'text' => 'Text',
        'email' => 'Email',
        'tel' => 'Phone',
        'textarea' => 'Textarea',
        'select' => 'Select Dropdown',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Button',
        'date' => 'Date',
        'number' => 'Number',
        'url' => 'URL'
    ];

    const MAPPABLE_FIELDS = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'company' => 'Company',
        'job_title' => 'Job Title',
        'industry' => 'Industry',
        'website' => 'Website',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'pinterest' => 'Pinterest',
        'city' => 'City',
        'state' => 'State',
        'zip' => 'ZIP',
        'country' => 'Country',
        'country_code' => 'Country Code',
        'country_iso_code' => 'Country ISO Code',
    ];

    const REQUIRED_FIELDS = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company'
    ];

    public function leadForm()
    {
        return $this->belongsTo(LeadForm::class, 'form_id');
    }

    public function getValidationRulesAttribute($value)
    {
        return $value ?: 'nullable';
    }
}
