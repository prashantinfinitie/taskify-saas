<?php

namespace Plugins\AssetManagement\Models;

use App\Models\User;
use App\Models\Admin;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'asset_tag',
        'description',
        'assigned_to',
        'category_id',
        'status',
        'purchase_date',
        'purchase_cost',
        'admin_id'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function registerMediaCollections(): void
    {
        $media_storage_settings = get_settings('media_storage_settings');
        $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';

        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('asset-media')->useDisk('s3');
        } else {
            $this->addMediaCollection('asset-media')->useDisk('public');
        }
    }

    public function admin(){
        return $this->belongsTo(Admin::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function histories()
    {
        return $this->hasMany(AssetHistory::class);
    }

    // Helper method to get current lending status
    public function getCurrentLending()
    {
        return $this->histories()
            ->where('action', 'Lent')
            ->whereNull('actual_return_date')
            ->with(['lentToUser', 'user'])
            ->first();
    }


    // Update isCurrentlyLent to use status
    public function isCurrentlyLent()
    {
        return $this->status === self::STATUS_LENT;
    }

    // Helper method to get the person who currently has the asset
    public function getCurrentHolder()
    {
        if ($this->isCurrentlyLent()) {
            return $this->getCurrentLending()->lentToUser;
        }
        return $this->assignedUser;
    }


    // Scope for available assets
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }


    // Add status constants for better maintainability
    const STATUS_AVAILABLE = 'available';
    const STATUS_LENT = 'lent';
    const STATUS_NON_FUNCTIONAL = 'non-functional';
    const STATUS_LOST = 'lost';
    const STATUS_DAMAGED = 'damaged';
    const STATUS_UNDER_MAINTENANCE = 'under-maintenance';

    // Add status helper methods
    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isLent()
    {
        return $this->status === self::STATUS_LENT;
    }



    // Add a method to get status badge class
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'bg-success',
            self::STATUS_LENT => 'bg-warning',
            self::STATUS_NON_FUNCTIONAL => 'bg-danger',
            self::STATUS_LOST => 'bg-dark',
            self::STATUS_DAMAGED => 'bg-danger',
            self::STATUS_UNDER_MAINTENANCE => 'bg-info',
            default => 'bg-secondary'
        };
    }

    public static function getStatusColor($status)
    {
        return match ($status) {
            self::STATUS_AVAILABLE => '#28a745',  // bg-success
            self::STATUS_LENT => '#ffc107',       // bg-warning
            self::STATUS_NON_FUNCTIONAL => '#dc3545', // bg-danger
            self::STATUS_LOST => '#343a40',       // bg-dark
            self::STATUS_DAMAGED => '#6c757d',    // bg-secondary
            self::STATUS_UNDER_MAINTENANCE => '#17a2b8', // bg-info
            default => '#6c757d',                 // bg-secondary
        };
    }
}
