<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Filament\Resources;

use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource\Pages\ListCannedResponses;
use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource\Pages\CreateCannedResponse;
use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource\Pages\EditCannedResponse;
use Crumbls\HelpDesk\Models;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CannedResponseResource extends Resource
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public static function getModelLabel(): string
    {
        return 'Canned Response';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Canned Responses';
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('helpdesk.filament.settings_navigation_group', 'Helpdesk Settings'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('helpdesk.filament.resources.canned_response.sort', TicketResource::getNavigationSort() + 6);
    }

    public static function getModel(): string
    {
        return Models::cannedResponse();
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

                        RichEditor::make('content')
                            ->required()
                            ->maxLength(65535),

                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'title')
                            ->nullable()
                            ->helperText('Leave blank for global responses'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.title')
                    ->label('Department')
                    ->default('Global')
                    ->sortable(),

                TextColumn::make('content')
                    ->limit(50)
                    ->html()
                    ->formatStateUsing(fn ($state) => Str::limit(strip_tags($state), 50))
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
            ])
            ->defaultSort('sort_order');
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
            'index' => ListCannedResponses::route('/'),
            'create' => CreateCannedResponse::route('/create'),
            'edit' => EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
