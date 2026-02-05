<?php

namespace Crumbls\HelpDesk\Traits;

trait HasColors
{
    public function initializeHasColors(): void
    {
        $this->fillable = array_merge(
            $this->fillable ?? [],
            ['color_background', 'color_foreground']
        );
    }

    protected static function bootHasColors(): void
    {
        static::saving(function ($model) {
            if ($model->color_background && !$model->color_foreground) {
                $model->color_foreground = static::getContrastColor($model->color_background);
            }
        });
    }

    public static function getContrastColor(string $hexcolor): string
    {
        $hex = ltrim($hexcolor, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (($r * 0.299) + ($g * 0.587) + ($b * 0.114)) / 255;

        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }

    public function getBackgroundColorAttribute(): string
    {
        return $this->color_background ?? '#000000';
    }

    public function getForegroundColorAttribute(): string
    {
        return $this->color_foreground ?? '#ffffff';
    }

    public function getColorSchemeAttribute(): array
    {
        return [
            'background' => $this->background_color,
            'foreground' => $this->foreground_color,
        ];
    }
}
