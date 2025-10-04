<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'workspace_id',
        'admin_id',
        'source_id',
        'stage_id',
        'assigned_to',
        'slug',
        'is_active',
        'success_message',
        'redirect_url'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($leadForm) {
            if (empty($leadForm->slug)) {
                $leadForm->slug = Str::uuid()->toString();
            }
        });
    }

    public function leadFormFields()
    {
        return $this->hasMany(LeadFormField::class, 'form_id');
    }

    public function requiredFields()
    {
        return $this->leadFormFields()->where('is_required', true);
    }

    public function customFields()
    {
        return $this->leadFormFields()->where('is_mapped', false);
    }

    public function mappedFields()
    {
        return $this->leadFormFields()->where('is_mapped', true);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function leadStage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'lead_form_id');
    }

    public function getPublicUrlAttribute()
    {
        return route('public.form', ['slug' => $this->slug]);
    }

    public function getEmbedCodeAttribute()
    {

        return '<iframe src="' . $this->public_url . '" frameborder="0" scrolling="yes" style="display:block; width:100%; height:60vh;"></iframe>';
    }
}
