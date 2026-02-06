<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Filament;

use Crumbls\HelpDesk\Filament\Resources\DepartmentResource;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource;
use Crumbls\HelpDesk\Filament\Resources\TicketResource;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource;
use Crumbls\HelpDesk\Filament\Resources\TicketTypeResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class HelpDeskPlugin implements Plugin
{
    public function getId(): string
    {
        return 'help-desk';
    }

    public function register(Panel $panel): void
    {
        $resourceClasses = array_column(
            (array) config('helpdesk.filament.resources', []),
            'class'
        );

        if (empty($resourceClasses)) {
            $resourceClasses = [
                DepartmentResource::class,
                PriorityResource::class,
                TicketResource::class,
                TicketStatusResource::class,
                TicketTypeResource::class,
            ];
        }

        $panel->resources($resourceClasses);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
