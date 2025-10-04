<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('defaultOrWorkspace', function (Builder $builder) {
            $workspaceId = getWorkspaceId(); // :repeat: Replace with your actual helper or logic
            $builder->where(function ($query) use ($workspaceId) {
                $query->where(function ($q) {
                    $q->where('is_default', true)->whereNull('workspace_id');
                })->orWhere('workspace_id', $workspaceId);
            });
            // dd($builder);
        });
    }
    protected $fillable = [
        'workspace_id',
        'admin_id',
        'name',
        'is_default'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'source_id');
    }
}
