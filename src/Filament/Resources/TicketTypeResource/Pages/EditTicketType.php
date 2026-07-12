<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketTypeResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\TicketTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketType extends EditRecord
{
    protected static string $resource = TicketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
