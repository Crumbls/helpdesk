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

class DepartmentResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

	public static function getModelLabel(): string
	{
		return __('helpdesk::departments.label');
	}

	public static function getPluralModelLabel(): string
	{
		return __('helpdesk::departments.plural');
	}

	public static function getNavigationGroup(): ?string
	{
		return __(config('helpdesk.filament.settings_navigation_group', 'Helpdesk Settings'));
	}

	public static function getNavigationSort(): ?int
	{
		return config('helpdesk.filament.resources.department.sort', TicketResource::getNavigationSort() + 5);
	}

	public static function getModel(): string
    {
        return Models::department();
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

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
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

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => \Crumbls\HelpDesk\Filament\Resources\DepartmentResource\Pages\ListDepartments::route('/'),
            'create' => \Crumbls\HelpDesk\Filament\Resources\DepartmentResource\Pages\CreateDepartment::route('/create'),
            'edit' => \Crumbls\HelpDesk\Filament\Resources\DepartmentResource\Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
