<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Models\Page;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Exception;

class PageResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Page::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-view-boards';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\Select::make('blueprint_id')
                    ->relationship('blueprint', 'name')
                    ->saveRelationshipsUsing(null)
                    ->required()
                    ->exists(Blueprint::class, 'id')
                    ->searchable()
                    ->preload(),
            ]),
        ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Page $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('configure')
                    ->icon('heroicon-s-cog')
                    ->url(fn (Page $record) => route('filament-tenant.resources.' . self::getSlug() . '.configure', $record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Resources\PageResource\Pages\ListPages::route('/'),
            'create' => Resources\PageResource\Pages\CreatePage::route('/create'),
            'edit' => Resources\PageResource\Pages\EditPage::route('/{record}/edit'),
            'configure' => Resources\PageResource\Pages\ConfigurePage::route('/{record}/configure'),
        ];
    }
}
