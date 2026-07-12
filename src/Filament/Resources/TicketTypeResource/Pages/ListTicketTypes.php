<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketTypeResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\TicketTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketTypes extends ListRecords
{
    protected static string $resource = TicketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
