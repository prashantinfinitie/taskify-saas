<?php

namespace Plugins\AssetManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'color','admin_id'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
