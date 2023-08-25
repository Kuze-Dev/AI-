<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Domain\Site\Models\Site;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Domain\Globals\Models\Globals;
use Illuminate\Support\Facades\Auth;
use Domain\Blueprint\Models\Blueprint;
use Domain\Internationalization\Models\Locale;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Illuminate\Validation\Rules\Unique;

class GlobalsResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Globals::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-globe';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->unique(
                        callback: function ($livewire, Unique $rule) {

                            if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {
                                return false;
                            }

                            return $rule;
                        },
                        ignoreRecord: true
                    )
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\Select::make('blueprint_id')
                    ->label(trans('Blueprint'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Blueprint::class, 'name')
                    ->disabled(fn (?Globals $record) => $record !== null)
                    ->reactive(),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) optional(Locale::where('is_default', true)->first())->code)
                    ->searchable()
                    ->hidden(Locale::count() === 1 || (bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),
                Forms\Components\Card::make([
                    Forms\Components\CheckboxList::make('sites')
                        ->rules([
                            function (?Globals $record, Closure $get) {

                                return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                    $siteIDs = $value;

                                    if ($record) {
                                        $siteIDs = array_diff($siteIDs, $record->sites->pluck('id')->toArray());

                                        $globals = Globals::where('name', $get('name'))
                                            ->where('id', '!=', $record->id)
                                            ->whereHas(
                                                'sites',
                                                fn ($query) => $query->whereIn('site_id', $siteIDs)
                                            )->count();

                                    } else {

                                        $globals = Globals::where('name', $get('name'))->whereHas(
                                            'sites',
                                            fn ($query) => $query->whereIn('site_id', $siteIDs)
                                        )->count();
                                    }

                                    if ($globals > 0) {
                                        $fail("Globals {$get('name')} is already available in selected sites.");
                                    }

                                };
                            },
                        ])
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->formatStateUsing(fn (?Globals $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) && Auth::user()?->hasRole(config('domain.role.super_admin')))),
                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    ->schemaData(fn (Closure $get) => ($get('blueprint_id') != null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->truncate('max-w-xs xl:max-w-md 2xl:max-w-2xl', true),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->relationship('sites', 'name'),
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->searchable()
                    ->optionsLimit(20),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => ListGlobals::route('/'),
            'create' => CreateGlobals::route('/create'),
            'edit' => EditGlobals::route('/{record}/edit'),
        ];
    }
}
