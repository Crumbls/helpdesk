<?php

namespace Crumbls\HelpDesk\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return null; // This will place it at the top level
    }
    
    public static function getNavigationSort(): ?int
    {
        return -2; // This will ensure it appears first
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // We'll add widgets here later
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            // We'll add widgets here later
        ];
    }
}
