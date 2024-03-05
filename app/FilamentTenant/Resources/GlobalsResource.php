<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class GlobalsResource extends Resource
{
    protected static ?string $model = Globals::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make([
                Forms\Components\TextInput::make('name')
                    ->unique(
                        modifyRuleUsing: function ($livewire, Unique $rule) {

                            if (TenantFeatureSupport::active(SitesManagement::class)) {
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
                    ->hidden(TenantFeatureSupport::inactive(Internationalization::class))
                    ->required(),
                Forms\Components\Section::make([
                    Forms\Components\CheckboxList::make('sites')
                        ->required(fn () => TenantFeatureSupport::active(SitesManagement::class))
                        ->rules([
                            function (?Globals $record, \Filament\Forms\Get $get) {

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
                    ->hidden((bool) ! (TenantFeatureSupport::active(SitesManagement::class) && Auth::user()?->hasRole(config('domain.role.super_admin')))),
                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    ->hidden(fn (?Globals $record) => ! $record)
                    ->schemaData(fn (\Filament\Forms\Get $get) => ($get('blueprint_id') != null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
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
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->hidden(TenantFeatureSupport::inactive(SitesManagement::class))
                    ->toggleable(condition: function () {
                        return TenantFeatureSupport::active(SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (TenantFeatureSupport::active(SitesManagement::class)))
                    ->relationship('sites', 'name'),
                Tables\Filters\SelectFilter::make('blueprint')
                    ->relationship('blueprint', 'name')
                    ->hidden((bool) ! Auth::user()?->can('blueprint.viewAny'))
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
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize(fn () => Auth::user()?->hasRole(config('domain.role.super_admin'))),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return Builder<\Domain\Globals\Models\Globals> */
    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            Auth::user()?->can('site.siteManager') &&
            ! (Auth::user()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', function ($q) {
                return $q->whereIn('site_id', Auth::user()?->userSite->pluck('id')->toArray());
            });
        }

        return static::getModel()::query();

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
