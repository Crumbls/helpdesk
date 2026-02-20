<?php

namespace Crumbls\HelpDesk\Filament\Resources\CannedResponseResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCannedResponse extends EditRecord
{
    protected static string $resource = CannedResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
