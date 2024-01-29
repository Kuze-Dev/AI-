<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\Filament\Rules\FullyQualifiedDomainNameRule;
use App\FilamentTenant\Resources\SiteResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Site\Models\Site;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\ActivityLogger;
use Throwable;

class SiteResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Site::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('domain')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->rules([new FullyQualifiedDomainNameRule()])
                        ->maxLength(100)
                        ->reactive()
                        ->formatStateUsing(function (?string $state): ?string {
                            return $state ? preg_replace('/^(http:\/\/|https:\/\/|www\.)/i', '', $state) : null;
                        })
                        ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/^(http:\/\/|https:\/\/|www\.)/i', '', $state) : null)
                        ->label(trans('Frontend Domain')),
                    Forms\Components\TextInput::make('deploy_hook'),
                    Forms\Components\Fieldset::make('Site Managers')
                        ->schema([
                            Forms\Components\CheckboxList::make('site_manager')
                                ->disableLabel()
                                ->columnSpanFull()
                                ->searchable()
                                ->formatStateUsing(fn (?Site $record) => $record ? $record->siteManager->pluck('id')->toArray() : [])
                                ->columns(2)
                                ->options(function () {
                                    return \Domain\Admin\Models\Admin::permission('site.siteManager')
                                        ->get()
                                        ->pluck('site_label', 'id')
                                        ->toArray();

                                }),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('deploy')
                    ->button()
                    ->icon('heroicon-o-cog')
                    ->color('secondary')
                    ->disabled(function (Site $record) {

                        return (bool) (is_null($record->deploy_hook));
                    })
                    ->action(function (Site $record) {

                        try {

                            if (is_null($record->deploy_hook)) {

                                Notification::make()
                                    ->danger()
                                    ->title(trans('No Deploy Hook Set for '.$record->name))
                                    ->body(trans('Please set a deploy hook first before trying to deploy.'))
                                    ->send();

                                return;
                            }

                            /** @var \Illuminate\Http\Client\Response $response */
                            $response = Http::post($record->deploy_hook);

                            tap(Notification::make(), function (Notification $notification) use ($response, $record) {
                                if ($exception = $response->toException()) {
                                    report($exception);
                                    $notification->danger()
                                        ->title(trans('Unable to Deploy Static Site'))
                                        ->body(trans('There was a problem when trying to request a deployment. Please try again later.'));

                                    return;
                                }

                                app(ActivityLogger::class)
                                    ->useLog('admin')
                                    ->event('deployed-hook')
                                    ->withProperties([
                                        'custom' => [
                                            'site' => $record->name,
                                            'deploy_hook' => $record->deploy_hook,
                                        ],
                                    ])
                                    ->log('Deployed hook '.$record->name);

                                $notification->success()
                                    ->title(trans('Deployment Request Sent'));
                            })->send();

                        } catch (Throwable $th) {

                            if ($th instanceof \Illuminate\Http\Client\ConnectionException) {
                                Notification::make()
                                    ->danger()
                                    ->title('Unable to Deploy Static Site')
                                    ->body($th->getMessage())
                                    ->send();
                            }

                        }

                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),

            ]);
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) ?: false;
    }

    /** @return Builder<Site> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
