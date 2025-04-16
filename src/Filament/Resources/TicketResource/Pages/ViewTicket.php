<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages;

use Crumbls\HelpDesk\Filament\Resources\TicketResource;
use Crumbls\HelpDesk\Filament\Resources\TicketResource\Widgets\TicketCommentsWidget;
use Crumbls\HelpDesk\Filament\Resources\TicketResource\Widgets\TicketMetadataWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    protected function getFooterWidgets(): array
    {
        return [
            TicketCommentsWidget::class,
            TicketMetadataWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }

    protected function getLayoutData(): array
    {
        return [
            'maxContentWidth' => 'full',
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
			'sm' => 1,
            'md' => 3
        ];
    }
}
