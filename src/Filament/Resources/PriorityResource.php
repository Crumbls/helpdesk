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

class PriorityResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-flag';

	public static function getModelLabel(): string
	{
		return __('helpdesk::priorities.label');
	}

	public static function getPluralModelLabel(): string
	{
		return __('helpdesk::priorities.plural');
	}

	public static function getNavigationGroup(): ?string
	{
		return __(config('helpdesk.filament.settings_navigation_group', 'Helpdesk Settings'));
	}

	public static function getNavigationSort(): ?int
	{
		return config('helpdesk.filament.resources.priority.sort', TicketTypeResource::getNavigationSort() + 5);
	}
    public static function getModel(): string
    {
        return Models::priority();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\ColorPicker::make('color_background')
                                    ->label('Background Color'),

                                Forms\Components\ColorPicker::make('color_foreground')
                                    ->label('Foreground Color')
                                    ->helperText('Auto-calculated from background if left blank.'),
                            ]),

                        Forms\Components\TextInput::make('level')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_default')
                                    ->label('Default')
                                    ->default(false),
                            ]),
                    ]),

                Forms\Components\Section::make('SLA Settings')
                    ->description('Service Level Agreement response and resolution times')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sla_response_hours')
                                    ->label('Response Time (hours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Hours until first response is due'),

                                Forms\Components\TextInput::make('sla_resolution_hours')
                                    ->label('Resolution Time (hours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Hours until ticket resolution is due'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span style="background-color: ' . e($record->background_color) . '; color: ' . e($record->foreground_color) . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->title) . '</span>'
                    ))
                    ->html()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_default')
                    ->label('Default')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sla_response_hours')
                    ->label('Response SLA')
                    ->suffix('h')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sla_resolution_hours')
                    ->label('Resolution SLA')
                    ->suffix('h')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('level', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
            'index' => \Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\ListPriorities::route('/'),
            'create' => \Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\CreatePriority::route('/create'),
            'edit' => \Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\EditPriority::route('/{record}/edit'),
        ];
    }
}
