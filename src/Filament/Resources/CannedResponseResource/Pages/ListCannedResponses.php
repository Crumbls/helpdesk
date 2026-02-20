<?php

namespace Crumbls\HelpDesk\Filament\Resources\CannedResponseResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCannedResponses extends ListRecords
{
    protected static string $resource = CannedResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
