<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketStatus extends EditRecord
{
    protected static string $resource = TicketStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
