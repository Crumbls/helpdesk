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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages\ListTicketStatuses;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages\CreateTicketStatus;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource\Pages\EditTicketStatus;
use Crumbls\HelpDesk\Models;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TicketStatusResource extends Resource
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';

	public static function getModelLabel(): string
	{
		return __('helpdesk::statuses.label');
	}

	public static function getPluralModelLabel(): string
	{
		return __('helpdesk::statuses.plural');
	}

	public static function getNavigationGroup(): ?string
	{
		return __(config('helpdesk.filament.settings_navigation_group', 'Helpdesk Settings'));
	}

	public static function getNavigationSort(): ?int
	{
		return config('helpdesk.filament.resources.status.sort', DepartmentResource::getNavigationSort() + 5);
	}

    public static function getModel(): string
    {
        return Models::status();
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

                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                Toggle::make('is_default')
                                    ->label('Default')
                                    ->default(false),

                                Toggle::make('is_closed')
                                    ->label('Closed Status')
                                    ->default(false)
                                    ->helperText('Tickets with this status are considered resolved.'),
                            ]),
                    ]),
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

                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->sortable(),

                ToggleColumn::make('is_closed')
                    ->label('Closed')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('is_closed')
                    ->label('Closed'),
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
            'index' => ListTicketStatuses::route('/'),
            'create' => CreateTicketStatus::route('/create'),
            'edit' => EditTicketStatus::route('/{record}/edit'),
        ];
    }
}
