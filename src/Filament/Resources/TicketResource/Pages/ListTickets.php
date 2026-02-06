<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages;

use Filament\Actions\CreateAction;
use Crumbls\HelpDesk\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
