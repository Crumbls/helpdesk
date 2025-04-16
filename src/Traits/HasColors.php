<?php

namespace Crumbls\HelpDesk\Traits;

use Illuminate\Support\Arr;

trait HasColors
{
    protected static array $defaultColors = [
        'primary' => '#3B82F6',   // blue-500
        'secondary' => '#6B7280', // gray-500
        'success' => '#10B981',   // green-500
        'danger' => '#EF4444',    // red-500
        'warning' => '#F59E0B',   // amber-500
        'info' => '#3B82F6',      // blue-500
    ];

    public function initializeHasColors()
    {
        $this->fillable = array_merge(
            $this->fillable ?? [],
            ['color_name', 'color_background', 'color_foreground']
        );
    }

    protected static function bootHasColors()
    {
        static::saving(function ($model) {
            if ($model->color_name && !$model->color_background) {
                $model->color_background = static::getColorFromName($model->color_name);
            }
            if ($model->color_name && !$model->color_foreground) {
                $model->color_foreground = static::getContrastColor($model->color_background ?? '#ffffff');
            }
        });
    }

    public static function getColorFromName(string $name): string
    {
        return static::$defaultColors[$name] ?? '#000000';
    }

    public static function getContrastColor(string $hexcolor): string
    {
        // Remove # if present
        $hex = ltrim($hexcolor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate luminance
        $luminance = (($r * 0.299) + ($g * 0.587) + ($b * 0.114)) / 255;
        
        // Return black or white based on luminance
        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }

    public function getBackgroundColorAttribute(): string
    {
        return $this->color_background ?? static::getColorFromName($this->color_name ?? 'primary');
    }

    public function getForegroundColorAttribute(): string
    {
        return $this->color_foreground ?? static::getContrastColor($this->background_color);
    }

    public function getColorSchemeAttribute(): array
    {
        return [
            'background' => $this->background_color,
            'foreground' => $this->foreground_color,
        ];
    }
}
