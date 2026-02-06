<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\ListPriorities;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\CreatePriority;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource\Pages\EditPriority;
use Crumbls\HelpDesk\Models;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PriorityResource extends Resource
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535),

                        Grid::make(2)
                            ->schema([
                                ColorPicker::make('color_background')
                                    ->label('Background Color'),

                                ColorPicker::make('color_foreground')
                                    ->label('Foreground Color')
                                    ->helperText('Auto-calculated from background if left blank.'),
                            ]),

                        TextInput::make('level')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                Toggle::make('is_default')
                                    ->label('Default')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('SLA Settings')
                    ->description('Service Level Agreement response and resolution times')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sla_response_hours')
                                    ->label('Response Time (hours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Hours until first response is due'),

                                TextInput::make('sla_resolution_hours')
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
                TextColumn::make('title')
                    ->formatStateUsing(fn ($record) => new HtmlString(
                        '<span style="background-color: ' . e($record->background_color) . '; color: ' . e($record->foreground_color) . '; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">' . e($record->title) . '</span>'
                    ))
                    ->html()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level')
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                ToggleColumn::make('is_default')
                    ->label('Default')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('sla_response_hours')
                    ->label('Response SLA')
                    ->suffix('h')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sla_resolution_hours')
                    ->label('Resolution SLA')
                    ->suffix('h')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('level', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
            'index' => ListPriorities::route('/'),
            'create' => CreatePriority::route('/create'),
            'edit' => EditPriority::route('/{record}/edit'),
        ];
    }
}
