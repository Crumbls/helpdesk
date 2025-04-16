<?php

namespace Crumbls\HelpDesk\Filament\Resources;

use Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages;
use Crumbls\HelpDesk\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationGroup = 'Helpdesk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\Select::make('ticket_type_id')
                            ->relationship('type', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('ticket_status_id')
                            ->relationship('status', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('priority_id')
                            ->relationship('priority', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('submitter_id')
                            ->relationship('submitter', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('parent_ticket_id')
                            ->relationship('parentTicket', 'title')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->nullable(),
                        Forms\Components\TextInput::make('source')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Content')
                    ->schema([
	                    Forms\Components\TextInput::make('title')
		                    ->required()
		                    ->maxLength(255)
		                    ->columnSpan(2),

	                    Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\RichEditor::make('resolution')
                            ->columnSpan(2)
                            ->visible(fn ($record) => $record && $record->closed_at !== null),
                    ])->columns(2),
                Forms\Components\Section::make('Assignments')
                    ->schema([
                        Forms\Components\Select::make('assignees')
                            ->relationship('assignees', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('watchers')
                            ->relationship('watchers', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.title')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->type?->color_name),
                Tables\Columns\TextColumn::make('status.title')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->status?->color_name),
                Tables\Columns\TextColumn::make('priority.title')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->priority?->color_name),
                Tables\Columns\TextColumn::make('department.title')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->department?->color_name),
                Tables\Columns\TextColumn::make('submitter.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignees.name')
                    ->badge()
                    ->separator(',')
                    ->limitList(2),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('closed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->relationship('type', 'title')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'title')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->relationship('priority', 'title')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'title')
                    ->preload()
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('closed')
                    ->placeholder('All tickets')
                    ->trueLabel('Closed tickets')
                    ->falseLabel('Open tickets')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('closed_at'),
                        false: fn ($query) => $query->whereNull('closed_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
