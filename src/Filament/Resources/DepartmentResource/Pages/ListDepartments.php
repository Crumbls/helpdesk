<?php

namespace Crumbls\HelpDesk\Filament\Resources\DepartmentResource\Pages;

use Filament\Actions\CreateAction;
use Crumbls\HelpDesk\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
