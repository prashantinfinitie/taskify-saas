<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = ['theme_name', 'is_active', 'theme_config'];

    protected $casts = [
        'theme_config' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getActiveTheme()
    {
        return self::where('is_active', true)->first()?->theme_name ?? 'new';
    }

    public static function setActiveTheme($themeName)
    {
        // Deactivate all themes
        self::query()->update(['is_active' => false]);

        // Activate selected theme
        return self::updateOrCreate(
            ['theme_name' => $themeName],
            ['is_active' => true]
        );
    }
}
