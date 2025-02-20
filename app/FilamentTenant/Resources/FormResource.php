<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\CMS\Internationalization;
use App\Features\CMS\SitesManagement;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\FormResource\Pages;
use App\FilamentTenant\Resources\FormResource\RelationManagers\FormSubmissionsRelationManager;
use App\FilamentTenant\Support\SchemaInterpolations;
use App\Settings\FormSettings;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Form\Models\Form as FormModel;
use Domain\Internationalization\Models\Locale;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class FormResource extends Resource
{
    protected static ?string $model = FormModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    // /** @return Builder<FormModel> */
    // protected static function getGlobalSearchEloquentQuery(): Builder
    // {
    //     return parent::getGlobalSearchEloquentQuery()->withCount('formSubmissions');
    // }

    /** @param  FormModel  $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return array_filter([
            'Total Submissions' => $record->form_submissions_count,
            'Selected Sites' => implode(',', $record->sites()->pluck('name')->toArray()),
        ]);
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        ->disableOptionWhen(fn (?FormModel $record) => $record !== null)
                        ->reactive(),
                    Forms\Components\Select::make('locale')
                        ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                        ->default((string) Locale::where('is_default', true)->first()?->code)
                        ->searchable()
                        ->hidden(TenantFeatureSupport::inactive(Internationalization::class))
                        ->required(),
                    Forms\Components\Toggle::make('store_submission'),
                    Forms\Components\Section::make([
                        // Forms\Components\CheckboxList::make('sites')
                        \App\FilamentTenant\Support\CheckBoxList::make('sites')
                            ->required(fn () => tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class))
                            ->rules([
                                fn (?FormModel $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                    $siteIDs = $value;

                                    if ($record) {
                                        $siteIDs = array_diff($siteIDs, $record->sites->pluck('id')->toArray());

                                        $form = FormModel::where('name', $get('name'))
                                            ->where('id', '!=', $record->id)
                                            ->whereHas(
                                                'sites',
                                                fn ($query) => $query->whereIn('site_id', $siteIDs)
                                            )->count();

                                    } else {

                                        $form = FormModel::where('name', $get('name'))->whereHas(
                                            'sites',
                                            fn ($query) => $query->whereIn('site_id', $siteIDs)
                                        )->count();
                                    }

                                    if ($form > 0) {
                                        $fail("Form {$get('name')} is already available in selected sites.");
                                    }

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
                            ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?FormModel $record): void {
                                if (! $record) {
                                    $component->state([]);

                                    return;
                                }

                                $component->state(
                                    $record->sites->pluck('id')
                                        ->intersect(array_keys($component->getOptions()))
                                        ->values()
                                        ->toArray()
                                );
                            }),
                    ])
                        ->hidden((bool) ! (tenancy()->tenant?->features()
                            ->active(
                                \App\Features\CMS\SitesManagement::class)
                            && Auth::user()?->hasRole(config('domain.role.super_admin'))
                        )),
                    Forms\Components\Toggle::make('uses_captcha')
                        ->disabled(fn (FormSettings $formSettings) => ! $formSettings->provider)
                        ->helperText(
                            fn (FormSettings $formSettings) => ! $formSettings->provider
                                ? trans('Currently unavailable. Please setup Captcha(in Settings > Form Settings) first.')
                                : null
                        ),
                ]),
                Forms\Components\Section::make([
                    Forms\Components\Section::make('Available Values')
                        ->schema([
                            SchemaInterpolations::make('data')
                                ->schemaData(fn (\Filament\Forms\Get $get) => Blueprint::where('id', $get('blueprint_id'))->first()?->schema),
                        ])
                        ->columnSpan(['md' => 1])
                        ->extraAttributes(['class' => 'md:sticky top-[5.5rem]']),
                    Forms\Components\Repeater::make('form_email_notifications')
                        ->afterStateHydrated(fn (Forms\Components\Repeater $component, ?FormModel $record) => $component->state($record?->formEmailNotifications->toArray() ?? []))
                        ->nullable()
                        ->schema([
                            Forms\Components\Section::make('Recipients')
                                ->schema([
                                    Forms\Components\TextInput::make('to')
                                        ->required()
                                        ->helperText('Seperated by comma')
                                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                            $component->state(implode(',', $state ?? []));
                                        })

                                        ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                            ? Str::of($state)
                                                ->split('/\,/')
                                                ->map(fn (string $rule) => trim($rule))
                                                ->toArray()
                                            : ($state ?? [])),
                                    Forms\Components\TextInput::make('cc')
                                        ->label(trans('CC'))
                                        ->nullable()
                                        ->helperText('Seperated by comma')
                                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                            $component->state(implode(',', $state ?? []));
                                        })
                                        ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                            ? Str::of($state)
                                                ->split('/\,/')
                                                ->map(fn (string $rule) => trim($rule))
                                                ->toArray()
                                            : ($state ?? [])),
                                    Forms\Components\TextInput::make('bcc')
                                        ->label(trans('BCC'))
                                        ->nullable()
                                        ->helperText('Seperated by comma')
                                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                            $component->state(implode(',', $state ?? []));
                                        })
                                        ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                            ? Str::of($state)
                                                ->split('/\,/')
                                                ->map(fn (string $rule) => trim($rule))
                                                ->toArray()
                                            : ($state ?? [])),
                                ])
                                ->columns(3),
                            Forms\Components\TextInput::make('sender')
                                ->default(app(FormSettings::class)->sender_email)
                                ->required(),
                            Forms\Components\TextInput::make('sender_name')
                                ->required(),
                            Forms\Components\TextInput::make('reply_to')
                                ->helperText('Seperated by comma')
                                ->nullable()
                                ->afterStateHydrated(function (Forms\Components\TextInput $component, ?array $state): void {
                                    $component->state(implode(',', $state ?? []));
                                })
                                ->dehydrateStateUsing(fn (string|array|null $state) => is_string($state)
                                    ? Str::of($state)
                                        ->split('/\,/')
                                        ->map(fn (string $rule) => trim($rule))
                                        ->toArray()
                                    : ($state ?? [])),
                            Forms\Components\TextInput::make('subject')
                                ->required()
                                ->nullable()
                                ->columnSpanFull(),
                            Forms\Components\MarkdownEditor::make('template')
                                ->required()
                                ->default(function (\Filament\Forms\Get $get) {
                                    $blueprint = Blueprint::whereId($get('../../blueprint_id'))->first();

                                    if ($blueprint === null) {
                                        return '';
                                    }

                                    $interpolations = '';

                                    foreach ($blueprint->schema->sections as $section) {
                                        foreach ($section->fields as $field) {
                                            $interpolations = "{$interpolations}{$field->title}: {{ \${$section->state_name}['{$field->state_name}'] }}\n";
                                        }
                                    }

                                    return <<<markdown
                                        Hi,

                                        We've received a new submission:

                                        {$interpolations}
                                        markdown;
                                })
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('has_attachments')
                                ->helperText('If Enabled Uploaded Files will be attach to this email notification'),
                        ])
                        ->columnSpan(['md' => 3]),
                ])->columns(4),

            ]);
    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->truncate('max-w-xs 2xl:max-w-xl', true),
                Tables\Columns\TextColumn::make('locale')
                    ->searchable()
                    ->hidden((bool) TenantFeatureSupport::inactive(Internationalization::class)),
                Tables\Columns\TextColumn::make('form_submissions_count')
                    ->badge()
                    ->counts('formSubmissions')
                    ->formatStateUsing(fn (FormModel $record, ?int $state) => $record->store_submission ? $state : 'N/A')
                    ->icon('heroicon-m-envelope')
                    ->color(fn (FormModel $record) => $record->store_submission ? 'success' : 'secondary'),
                Tables\Columns\TextColumn::make('sites.name')
                    ->badge()
                    ->toggleable(condition: fn () => TenantFeatureSupport::active(SitesManagement::class), isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sites')
                    ->multiple()
                    ->hidden((bool) ! (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)))
                    ->relationship('sites', 'name', function (Builder $query) {

                        if (Auth::user()?->can('site.siteManager') &&
                        ! (Auth::user()->hasRole(config('domain.role.super_admin')))) {
                            return $query->whereIn('id', Auth::user()->userSite->pluck('id')->toArray());
                        }

                        return $query;

                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize(fn () => Auth::user()?->hasRole(config('domain.role.super_admin'))),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return Builder<\Domain\Form\Models\Form> */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
            return static::getModel()::query();
        }

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            Auth::user()?->can('site.siteManager') &&
            ! (Auth::user()->hasRole(config('domain.role.super_admin')))
        ) {
            return static::getModel()::query()->wherehas('sites', fn ($q) => $q->whereIn('site_id', Auth::user()?->userSite->pluck('id')->toArray()));
        }

        return static::getModel()::query();

    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Main', [
                FormSubmissionsRelationManager::class,
                ActivitiesRelationManager::class,
            ]),
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
