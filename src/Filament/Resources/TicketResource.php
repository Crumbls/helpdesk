<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\ListTickets;
use Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\CreateTicket;
use Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\EditTicket;
use Carbon\Carbon;
use Crumbls\HelpDesk\Models;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TicketResource extends Resource
{
	public static function getModelLabel(): string
	{
		return __('helpdesk::tickets.label');
	}

	public static function getPluralModelLabel(): string
	{
		return __('helpdesk::tickets.plural');
	}

	public static function getNavigationGroup(): ?string
	{
		return __(config('helpdesk.filament.navigation_group', 'Helpdesk'));
	}

	protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

	public static function getNavigationSort(): ?int
	{
		return config('helpdesk.filament.resources.ticket.sort', 100);
	}

	public static function getModel(): string
    {
        return Models::ticket();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                Select::make('ticket_type_id')
                                    ->label('Type')
                                    ->relationship('type', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('ticket_status_id')
                                    ->label('Status')
                                    ->relationship('status', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('priority_id')
                                    ->label('Priority')
                                    ->relationship('priority', 'title')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'title')
                                    ->searchable()
                                    ->preload(),

                                Select::make('submitter_id')
                                    ->label('Submitter')
                                    ->relationship('submitter', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Select::make('parent_ticket_id')
                            ->label('Parent Ticket')
                            ->relationship('parentTicket', 'title')
                            ->searchable()
                            ->preload(),

                        TextInput::make('source')
                            ->maxLength(255)
                            ->default('web'),
                    ]),

                Section::make('Dates & Resolution')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('due_at')
                                    ->label('Due Date'),

                                DateTimePicker::make('closed_at')
                                    ->label('Closed Date'),
                            ]),

                        Textarea::make('resolution')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Comments')
                    ->schema([
                        Repeater::make('comments')
                            ->relationship(
                                name: 'comments',
                                modifyQueryUsing: fn (Builder $query) => $query->with('user')->orderBy('created_at', 'asc')
                            )
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Placeholder::make('user_display')
                                            ->label('Author')
                                            ->content(fn ($record): string =>
                                                $record?->user?->name ?? 'You'
                                            ),

                                        Placeholder::make('created_display')
                                            ->label('Posted')
                                            ->content(fn ($record): string =>
                                                $record?->created_at?->format('M j, Y g:i A') ?? '-'
                                            ),

                                        Placeholder::make('updated_display')
                                            ->label('Last Updated')
                                            ->content(fn ($record): string =>
                                                $record?->updated_at?->diffForHumans() ?? '-'
                                            ),
                                    ])
                                    ->visible(fn ($record): bool => $record !== null),

                                RichEditor::make('content')
                                    ->label(fn ($record): string => $record ? 'Edit Comment' : 'New Comment')
                                    ->required()
                                    ->disableToolbarButtons(['attachFiles'])
                                    ->disabled(fn ($record): bool => $record !== null && Gate::denies('update', $record))
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_private')
                                            ->label('Private')
                                            ->helperText('Only visible to staff')
                                            ->default(false)
                                            ->inline(false)
                                            ->disabled(fn ($record): bool => $record !== null && Gate::denies('update', $record)),

                                        Toggle::make('is_resolution')
                                            ->label('Resolution')
                                            ->helperText('Marks this as the ticket resolution')
                                            ->default(false)
                                            ->inline(false)
                                            ->disabled(fn ($record): bool => $record !== null && Gate::denies('update', $record)),
                                    ]),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['user_id'] = auth()->id();
                                return $data;
                            })
                            ->reorderable(false)
                            ->addActionLabel('Add Comment')
                            ->deleteAction(
                                fn (Action $action) => $action
                                    ->requiresConfirmation()
                                    ->authorize('delete')
                            )
                            ->itemLabel(function (array $state): ?string {
                                $flags = collect([
                                    ($state['is_private'] ?? false) ? 'Private' : null,
                                    ($state['is_resolution'] ?? false) ? 'Resolution' : null,
                                ])->filter();

                                $date = isset($state['created_at'])
                                    ? Carbon::parse($state['created_at'])->format('M j, Y g:i A')
                                    : null;

                                $preview = isset($state['content'])
                                    ? Str::limit(strip_tags($state['content']), 50)
                                    : null;

                                if (!$date && !$preview) {
                                    return 'New Comment';
                                }

                                $label = $flags->isNotEmpty()
                                    ? '[' . $flags->join(', ') . '] '
                                    : '';

                                $label .= $date ?? '';

                                if ($preview) {
                                    $label .= $date ? ' - ' : '';
                                    $label .= $preview;
                                }

                                return $label;
                            })
                            ->collapsible()
                            ->collapsed(fn ($record): bool => $record !== null)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record): bool => $record === null)
                    ->visible(fn ($record): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                TextColumn::make('status.title')
                    ->label('Status')
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span style="background-color: ' . e($record->status?->background_color ?? '#6B7280') . '; color: ' . e($record->status?->foreground_color ?? '#ffffff') . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->status?->title ?? '-') . '</span>'
                    ))
                    ->html()
                    ->sortable(),

                TextColumn::make('priority.title')
                    ->label('Priority')
                    ->formatStateUsing(fn ($record) => $record->priority
                        ? new HtmlString(
                            '<span style="background-color: ' . e($record->priority->background_color) . '; color: ' . e($record->priority->foreground_color) . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->priority->title) . '</span>'
                        )
                        : '-'
                    )
                    ->html()
                    ->sortable(),

                TextColumn::make('type.title')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department.title')
                    ->label('Department')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('submitter.name')
                    ->label('Submitter')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->relationship('status', 'title'),

                SelectFilter::make('priority_id')
                    ->label('Priority')
                    ->relationship('priority', 'title'),

                SelectFilter::make('ticket_type_id')
                    ->label('Type')
                    ->relationship('type', 'title'),

                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'title'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}
