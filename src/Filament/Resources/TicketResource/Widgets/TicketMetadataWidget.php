<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;

class TicketMetadataWidget extends Widget
{
    protected static string $view = 'helpdesk::widgets.ticket-metadata';

	protected int | string | array $columnSpan = [
		'default' => 'full',
		'md' => 2,
		'xl' => 3,
	];



	public ?Model $record = null;

    public function mount(?Model $record = null): void
    {
        $this->record = $record;
    }

    public function getTicket()
    {
        return $this->record;
    }

    public function render(): View
    {
        return view(static::$view, [
            'record' => $this->record,
        ]);
    }
}
