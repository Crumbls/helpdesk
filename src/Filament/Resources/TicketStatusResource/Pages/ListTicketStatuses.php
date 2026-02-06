<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages;

use Filament\Actions\CreateAction;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketStatuses extends ListRecords
{
    protected static string $resource = TicketStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
