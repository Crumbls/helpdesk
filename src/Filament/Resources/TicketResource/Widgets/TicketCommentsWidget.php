<?php

namespace Crumbls\HelpDesk\Filament\Resources\TicketResource\Widgets;

use Crumbls\HelpDesk\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\View\View;

class TicketCommentsWidget extends Widget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'helpdesk::widgets.ticket-comments';


	protected int | string | array $columnSpan = [
		'default' => 'full',
		'md' => 2,
		'xl' => 3,
	];

    public ?array $data = [];

    public ?Model $record = null;

    public function mount(?Model $record = null): void
    {
        $this->record = $record;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->label('Comment'),
                Forms\Components\Toggle::make('is_private')
                    ->label('Internal Note')
                    ->default(false),
                Forms\Components\Toggle::make('is_resolution')
                    ->label('Mark as Resolution')
                    ->default(false),
            ])
	        ->statePath('data');
    }

    public function addComment(): void
    {
        $data = $this->form->getState();
        
        $this->getTicket()->comments()->create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        $this->data = [
            'content' => '',
            'is_private' => false,
            'is_resolution' => false,
        ];

        $this->form->fill();

        $this->dispatch('comment-added');
    }

    #[Computed]
    public function getTicket()
    {
        return $this->record;
    }

    public function render(): View
    {
        return view(static::$view, [
            'ticket' => $this->record,
        ]);
    }
}
