<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-table';

    protected static ?int $navigationSort = 2;

    /** @throws \Spatie\Activitylog\Exceptions\InvalidConfiguration */
    public static function getModel(): string
    {
        return ActivitylogServiceProvider::determineActivityModel();
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('System');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')
                    ->label('Log')
                    ->translateLabel()
                    ->formatStateUsing(fn (string $state) => Str::headline($state))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('causer.full_name')
                    ->label('Causer')
                    ->translateLabel()
                    ->formatStateUsing(fn ($record) => $record->causer?->full_name)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subject_type')
                    ->formatStateUsing(fn (?string $state) => (string) Str::of(Relation::getMorphedModel($state ?? '') ?? '')->classBasename()->headline())
                    ->translateLabel(),
                Forms\Components\TextInput::make('subject_id')
                    ->translateLabel(),
                Forms\Components\KeyValue::make('properties.old')
                    ->visible(fn ($state) => filled($state))
                    ->translateLabel(),
                Forms\Components\KeyValue::make('properties.attributes')
                    ->visible(fn ($state) => filled($state))
                    ->label('New')
                    ->translateLabel(),
                Forms\Components\KeyValue::make('data')
                    ->formatStateUsing(
                        fn (Activity $record) => Arr::except($record->properties?->toArray() ?? [], ['old', 'attributes'])
                    )
                    ->visible(fn ($state) => filled($state))
                    ->label(trans('Properties'))
                    ->columnSpanFull(),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Log')
                    ->translateLabel()
                    ->formatStateUsing(fn (string $state) => Str::headline($state)),
                Tables\Columns\BadgeColumn::make('event')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('description')
                    ->translateLabel()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->hidden(fn ($livewire) => $livewire instanceof ActivitiesRelationManager)
                    ->getStateUsing(
                        function (Activity $record) {
                            if ($record->subject === null) {
                                return;
                            }

                            /** @var \Filament\Resources\Resource|null $resource */
                            $resource = self::findResourceForModel($record->subject::class);

                            return $resource
                                ? Str::headline($resource::getModelLabel())
                                : Str::of($record->subject::class)->classBasename()->headline();
                        }
                    )
                    ->url(
                        function (Activity $record) {
                            if ($record->subject === null) {
                                return;
                            }

                            /** @var \Filament\Resources\Resource|null $resource */
                            $resource = self::findResourceForModel($record->subject::class);

                            if ( ! $resource) {
                                return;
                            }

                            try {
                                if ($resource::hasPage('view')) {
                                    return $resource::getUrl('view', ['record' => $record->subject]);
                                }
                                if ($resource::hasPage('edit')) {
                                    return $resource::getUrl('edit', ['record' => $record->subject]);
                                }
                            } catch (UrlGenerationException) {
                            }
                        },
                        shouldOpenInNewTab: true
                    ),
                Tables\Columns\TextColumn::make('causer.full_name')
                    ->translateLabel(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Log')
                    ->options(self::getModel()::distinct()->pluck('log_name')->mapWithKeys(fn ($value) => [$value => Str::headline($value)]))
                    ->default('admin'),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->translateLabel(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }

    private static function findResourceForModel(string $model): ?string
    {
        return collect(Filament::getResources())
            ->first(fn ($resource) => $resource::getModel() === $model);
    }
}
