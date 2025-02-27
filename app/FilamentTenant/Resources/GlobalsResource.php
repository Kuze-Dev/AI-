<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use App\FilamentTenant\Resources\GlobalsResource\RelationManagers\GlobalsTranslationRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class GlobalsResource extends Resource
{
    protected static ?string $model = Globals::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    /** @param  Globals  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return array_filter([
            'Global' => $record->name,
            'Selected Sites' => implode(',', $record->sites()->pluck('name')->toArray()),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make([
                Forms\Components\TextInput::make('name')
                    ->unique(
                        modifyRuleUsing: function ($livewire, Unique $rule) {

                            if (
                                tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) ||
                                tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)
                            ) {
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
                    ->disableOptionWhen(fn (?Globals $record) => $record !== null)
                    ->helperText(fn (?Globals $record) => $record !== null ? 'Updating of Blueprint is Restricted' : null)
                    // ->disabled(fn (?Globals $record) => $record !== null)
                    ->reactive(),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->rules([
                        function (?Globals $record, Forms\Get $get) {

                            return function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                if ($record) {
                                    $selectedLocale = $value;

                                    $originalContentId = $record->translation_id ?: $record->id;

                                    $exist = Globals::where(fn ($query) => $query->where('translation_id', $originalContentId)->orWhere('id', $originalContentId)
                                    )->where('locale', $selectedLocale)->first();

                                    if ($exist && $exist->id != $record->id) {
                                        $fail("Global {$get('name')} has a existing ({$selectedLocale}) translation.");
                                    }
                                }

                            };
                        },
                    ])
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),
                Forms\Components\Section::make([
                    // Forms\Components\CheckboxList::make('sites')
                    \App\FilamentTenant\Support\CheckBoxList::make('sites')
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                        ->rules([
                            fn (?Globals $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {

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

                            },
                        ])
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                            $admin = filament_admin();

                            if ($admin->hasRole(config('domain.role.super_admin'))) {
                                return false;
                            }

                            $user_sites = $admin->userSite->pluck('id')->toArray();

                            $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                            return ! in_array($value, $intersect);
                        })
                        ->formatStateUsing(fn (?Globals $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))),
                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    // ->hidden(fn (?Globals $record) => ! $record)
                    ->schemaData(fn (\Filament\Forms\Get $get) => ($get('blueprint_id') != null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
            ]),
        ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->lineClamp(1)
                    ->wrap(),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->hidden(TenantFeatureSupport::inactive(SitesManagement::class))
                    ->toggleable(condition: fn () => TenantFeatureSupport::active(SitesManagement::class), isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name', function (Builder $query) {

                        if (filament_admin()->can('site.siteManager') &&
                        ! (filament_admin()->hasRole(config('domain.role.super_admin')))) {
                            return $query->whereIn('id', filament_admin()->userSite->pluck('id')->toArray());
                        }

                        return $query;

                    }),
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->hidden((bool) ! filament_admin()->can('blueprint.viewAny'))
                    ->searchable()
                    ->optionsLimit(20),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->hidden((bool) (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->default(Locale::where('is_default', 1)->first()?->code),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize(fn () => filament_admin()->hasRole(config('domain.role.super_admin'))),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return Builder<\Domain\Globals\Models\Globals> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        if (filament_admin()->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            filament_admin()->can('site.siteManager') &&
            ! (filament_admin()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', fn ($q) => $q->whereIn('site_id', filament_admin()->userSite->pluck('id')->toArray()));
        }

        return static::getModel()::query();

    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
            GlobalsTranslationRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListGlobals::route('/'),
            'create' => CreateGlobals::route('/create'),
            'edit' => EditGlobals::route('/{record}/edit'),
        ];
    }
}
