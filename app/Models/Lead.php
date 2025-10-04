<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'admin_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'country_code',
        'country_iso_code',
        'phone',
        'source_id',
        'stage_id',
        'created_by',
        'job_title',
        'company',
        'industry',
        'website',
        'linkedin',
        'instagram',
        'facebook',
        'pinterest',
        'city',
        'state',
        'zip',
        'country',
        'is_converted',
        'converted_at',
        'custom_fields',
        'lead_form_id'
    ];

    // Relationships

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }




    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }
    public function follow_ups()
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    public function form()
    {
        return $this->belongsTo(LeadForm::class, 'lead_form_id');
    }
}
