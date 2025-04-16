<?php

namespace Crumbls\HelpDesk\Filament\Resources;

use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages;
use Crumbls\HelpDesk\Models\TicketStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketStatusResource extends Resource
{
    protected static ?string $model = TicketStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    
    protected static ?string $navigationGroup = 'Helpdesk Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('color_name')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Forms\Components\Toggle::make('is_default')
                            ->required()
                            ->default(false),
                        Forms\Components\Toggle::make('is_closed')
                            ->required()
                            ->default(false)
                            ->helperText('Tickets with this status will be considered closed'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (TicketStatus $record): string => $record->color_name),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\ColorColumn::make('background_color')
                    ->label('Color')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_closed')
                    ->boolean()
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
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_closed'),
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
            'index' => Pages\ListTicketStatuses::route('/'),
            'create' => Pages\CreateTicketStatus::route('/create'),
            'edit' => Pages\EditTicketStatus::route('/{record}/edit'),
        ];
    }
}
