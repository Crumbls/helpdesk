<?php

namespace Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages;

use Filament\Actions\CreateAction;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriorities extends ListRecords
{
    protected static string $resource = PriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
