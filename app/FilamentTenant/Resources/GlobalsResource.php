<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use App\FilamentTenant\Resources\GlobalsResource\RelationManagers\GlobalsTranslationRelationManager;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class GlobalsResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Globals::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->unique(
                        callback: function ($livewire, Unique $rule) {

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
                    ->disabled(fn (?Globals $record) => $record !== null)
                    ->reactive(),
                Forms\Components\Select::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->default((string) Locale::where('is_default', true)->first()?->code)
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                    ->required(),
                Forms\Components\Card::make([
                    // Forms\Components\CheckboxList::make('sites')
                    \App\FilamentTenant\Support\CheckBoxList::make('sites')
                        ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
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
                        ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                            /** @var \Domain\Admin\Models\Admin */
                            $user = Auth::user();

                            if ($user->hasRole(config('domain.role.super_admin'))) {
                                return false;
                            }

                            $user_sites = $user->userSite->pluck('id')->toArray();

                            $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                            return ! in_array($value, $intersect);
                        })
                        ->formatStateUsing(fn (?Globals $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))),
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
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class)),
                Tables\Columns\TagsColumn::make('sites.name')
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->toggleable(condition: function () {
                        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class);
                    }, isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
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
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                    ->hidden((bool) (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->default(Locale::where('is_default', 1)->first()?->code),
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

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
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
            GlobalsTranslationRelationManager::class,
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
