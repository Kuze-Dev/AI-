<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Str;

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
                Forms\Components\Card::make([
                    Forms\Components\Toggle::make('published_dates')
                        ->reactive()
                        ->afterStateHydrated(fn (?Page $record, Closure $set) => $set('published_dates', $record && $record->past_behavior && $record->future_behavior)),
                    Forms\Components\Section::make('Behavior')
                        ->schema([
                            Forms\Components\Select::make('past_behavior')
                                ->required()
                                ->enum(PageBehavior::class)
                                ->options(self::getPageBehaviorOptions()),

                            Forms\Components\Select::make('future_behavior')
                                ->required()
                                ->enum(PageBehavior::class)
                                ->options(self::getPageBehaviorOptions()),
                        ])
                        ->when(fn (array $state) => $state['published_dates'])
                        ->columns(),
                ]),
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
                Tables\Columns\BadgeColumn::make('past_behavior')
                    ->formatStateUsing(fn (Page $record) => Str::headline($record->past_behavior?->value ?? ''))
                    ->color(fn (Page $record) => self::getPageBehaviorColors($record->past_behavior))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('future_behavior')
                    ->formatStateUsing(fn (Page $record) => Str::headline($record->future_behavior?->value ?? ''))
                    ->color(fn (Page $record) => self::getPageBehaviorColors($record->future_behavior))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(trans('Published date'))
                    ->date(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\TernaryFilter::make('published_at')
                    ->label(trans('Published date'))
                    ->nullable(),
                Tables\Filters\SelectFilter::make('has_behavior')
                    ->options(['1' => 'Yes', '0' => 'No'])
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['value']), function (Builder $query) use ($data) {
                            $query->when(filled($data['value']), function (Builder $query) use ($data) {
                                /** @var Page|Builder $query */
                                match ($data['value']) {
                                    '1' => $query->whereNotNull('past_behavior')->whereNotNull('future_behavior'),
                                    '0' => $query->whereNull('past_behavior')->whereNull('future_behavior'),
                                    default => '',
                                };
                            });
                        });
                    }),
                Tables\Filters\SelectFilter::make('past_behavior')
                    ->multiple()
                    ->options(self::getPageBehaviorOptions())
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['values']), function (Builder $query) use ($data) {
                            /** @var Page|Builder $query */
                            $query->whereIn('past_behavior', $data['values']);
                        });
                    }),
                Tables\Filters\SelectFilter::make('future_behavior')
                    ->multiple()
                    ->options(self::getPageBehaviorOptions())
                    ->query(function (Builder $query, array $data) {
                        $query->when(filled($data['values']), function (Builder $query) use ($data) {
                            /** @var Page|Builder $query */
                            $query->whereIn('future_behavior', $data['values']);
                        });
                    }),
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

    public static function getPageBehaviorOptions(): array
    {
        return collect(PageBehavior::cases())
            ->mapWithKeys(fn (PageBehavior $fieldType) => [
                $fieldType->value => Str::headline($fieldType->value),
            ])
            ->toArray();
    }

    public static function getPageBehaviorColors(?PageBehavior $pageBehavior): ?string
    {
        return match ($pageBehavior) {
            PageBehavior::PUBLIC => 'success',
            PageBehavior::UNLISTED => 'warning',
            PageBehavior::HIDDEN => 'danger',
            default => null,
        };
    }
}
