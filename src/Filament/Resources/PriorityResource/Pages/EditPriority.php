<?php

namespace Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\PriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriority extends EditRecord
{
    protected static string $resource = PriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
