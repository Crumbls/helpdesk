<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Filament\Resources;

use Crumbls\HelpDesk\Models;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Helpdesk';

    public static function getModel(): string
    {
        return Models::ticket();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('ticket_type_id')
                                    ->label('Type')
                                    ->relationship('type', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('ticket_status_id')
                                    ->label('Status')
                                    ->relationship('status', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('priority_id')
                                    ->label('Priority')
                                    ->relationship('priority', 'title')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'title')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('submitter_id')
                                    ->label('Submitter')
                                    ->relationship('submitter', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Select::make('parent_ticket_id')
                            ->label('Parent Ticket')
                            ->relationship('parentTicket', 'title')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('source')
                            ->maxLength(255)
                            ->default('web'),
                    ]),

                Forms\Components\Section::make('Dates & Resolution')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('due_at')
                                    ->label('Due Date'),

                                Forms\Components\DateTimePicker::make('closed_at')
                                    ->label('Closed Date'),
                            ]),

                        Forms\Components\Textarea::make('resolution')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('status.title')
                    ->label('Status')
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span style="background-color: ' . e($record->status?->background_color ?? '#6B7280') . '; color: ' . e($record->status?->foreground_color ?? '#ffffff') . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->status?->title ?? '-') . '</span>'
                    ))
                    ->html()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority.title')
                    ->label('Priority')
                    ->formatStateUsing(fn ($record) => $record->priority
                        ? new HtmlString(
                            '<span style="background-color: ' . e($record->priority->background_color) . '; color: ' . e($record->priority->foreground_color) . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->priority->title) . '</span>'
                        )
                        : '-'
                    )
                    ->html()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type.title')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.title')
                    ->label('Department')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('submitter.name')
                    ->label('Submitter')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('ticket_status_id')
                    ->label('Status')
                    ->relationship('status', 'title'),

                Tables\Filters\SelectFilter::make('priority_id')
                    ->label('Priority')
                    ->relationship('priority', 'title'),

                Tables\Filters\SelectFilter::make('ticket_type_id')
                    ->label('Type')
                    ->relationship('type', 'title'),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'title'),
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
            'index' => \Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\ListTickets::route('/'),
            'create' => \Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\CreateTicket::route('/create'),
            'edit' => \Crumbls\HelpDesk\Filament\Resources\TicketResource\Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
