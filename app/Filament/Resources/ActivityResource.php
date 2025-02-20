<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\ActivityLog\ActivityLogEvent;
use App\Support\ActivityLog\ActivityLogName;
use App\Support\ActivityLog\ActivitySubjectType;
use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use ErrorException;
use Exception;
use Filament\Facades\Filament;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    /** @throws \Spatie\Activitylog\Exceptions\InvalidConfiguration */
    #[\Override]
    public static function getModel(): string
    {
        return ActivitylogServiceProvider::determineActivityModel();
    }

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('Access');
    }

    #[\Override]
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Infolists\Components\TextEntry::make('description')
                    ->translateLabel()
                    ->columnSpanFull(),

                Infolists\Components\TextEntry::make('subject')
                    ->translateLabel()
                    ->state(
                        function (Activity $record): ?string {
                            if ($record->subject === null) {
                                return null;
                            }

                            /** @var \Filament\Resources\Resource|null $resource */
                            $resource = collect(Filament::getResources())
                                ->first(fn (mixed $resource) => $resource::getModel() === $record->subject::class);

                            return $resource === null
                                ? (string) Str::of($record->subject::class)->classBasename()->headline()
                                : sprintf('%s: %s', Str::headline($resource::getModelLabel()), $record->subject->getRouteKey());
                        }
                    )
                    ->url(
                        function (Activity $record): ?string {
                            if ($record->subject === null) {
                                return null;
                            }

                            /** @var \Filament\Resources\Resource|null $resource */
                            $resource = collect(Filament::getResources())
                                ->first(fn (mixed $resource) => $resource::getModel() === $record->subject::class);

                            if ($resource === null) {
                                return null;
                            }
                            try {
                                if ($resource::hasPage('view') && $resource::canView($record->subject)) {
                                    return $resource::getUrl('view', ['record' => $record->subject]);
                                }
                                if ($resource::hasPage('edit') && $resource::canEdit($record->subject)) {
                                    return $resource::getUrl('edit', ['record' => $record->subject]);
                                }
                            } catch (UrlGenerationException) {
                            }

                            return null;
                        },
                        shouldOpenInNewTab: true
                    )
                    ->placeholder('--'),

                Infolists\Components\TextEntry::make('causer')
                    ->translateLabel()
                    ->state(function (Activity $record): ?string {
                        if ($record->causer === null) {
                            return null;
                        }

                        return match ($record->causer::class) {
                            Admin::class => trans('Admin: :admin', ['admin' => $record->causer->full_name]),
                            Customer::class => trans('User: :user', [
                                'user' => $record->causer->full_name,
                            ]),
                            default => throw new ErrorException(
                                'No matching model `'.$record->causer::class.'` for activity causer.'
                            ),
                        };
                    })
//                    ->url(function (Activity $record): ?string {
//                        if (null === $record->causer) {
//                            return null;
//                        }
//
//                        return match ($record->causer::class) {
//                            Admin::class => AdminResource::canEdit($record->causer)
//                                ? AdminResource::getUrl('edit', [$record->causer])
//                                : null,
//                            Customer::class => CustomerResource::canEdit($record->causer)
//                                ? CustomerResource::getUrl('edit', [$record->causer])
//                                : null,
//                            default => throw new ErrorException(
//                                'No matching model `'.$record->causer::class.'` for activity causer.'
//                            ),
//                        };
//                    },
//                        shouldOpenInNewTab: true
//                    )
                    ->placeholder('--'),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Logged at')
                    ->translateLabel()
                    ->dateTime()
                    ->helperText(fn (Activity $record) => $record->created_at?->diffForHumans()),

                Infolists\Components\Section::make()
                    ->description(trans('Payload'))
                    ->visible(
                        fn (Activity $record): bool => ActivityLogEvent::isApiPayload($record->event)
                    )
                    ->schema([

                        Infolists\Components\TextEntry::make('properties')
                            ->hiddenLabel()
                            ->inlineLabel(false)
                            ->state(fn (Activity $record): ?string => $record
                                ->properties?->toJson()
                            )
                            ->helperText('use this https://onlinejsonformatter.com/'),

                        //                        Infolists\Components\RepeatableEntry::make('data')
                        //                            ->hiddenLabel()
                        //                            ->state(
                        //                                fn (Activity $record): ?Collection => $record
                        //                                    ->properties
                        //                                    ?->except('old', 'attributes')
                        //                            )
                        //                            ->schema(
                        //                                fn (?Collection $state): array => $state
                        //                                    ?->map(
                        //                                        fn (string $value, string $property): Infolists\Components\TextEntry => Infolists\Components\TextEntry::make($property)
                        //                                            ->color('primary')
                        //                                            ->state($value)
                        //                                            ->inlineLabel()
                        //                                    )
                        //                                    ->toArray() ?? []
                        //                            )
                        //                            ->contained(false),

                    ]),

                Infolists\Components\Section::make()
                    ->description(trans('Properties'))
                    ->visible(
                        fn (Activity $record): bool => ! ActivityLogEvent::isApiPayload($record->event) && (
                                $record
                                    ->properties
                                    ?->except('old', 'attributes')
                                    ->isNotEmpty() ?? false
                            )
                    )
                    ->schema([

                        Infolists\Components\KeyValueEntry::make('properties')
                            ->hiddenLabel()
                            ->inlineLabel(false)
                            ->state(fn (Activity $record): ?Collection => $record
                                ->properties
                                ?->except('old', 'attributes')
                            ),

                        //                        Infolists\Components\RepeatableEntry::make('data')
                        //                            ->hiddenLabel()
                        //                            ->state(
                        //                                fn (Activity $record): ?Collection => $record
                        //                                    ->properties
                        //                                    ?->except('old', 'attributes')
                        //                            )
                        //                            ->schema(
                        //                                fn (?Collection $state): array => $state
                        //                                    ?->map(
                        //                                        fn (string $value, string $property): Infolists\Components\TextEntry => Infolists\Components\TextEntry::make($property)
                        //                                            ->color('primary')
                        //                                            ->state($value)
                        //                                            ->inlineLabel()
                        //                                    )
                        //                                    ->toArray() ?? []
                        //                            )
                        //                            ->contained(false),

                    ]),

                Infolists\Components\Section::make()
                    ->description(trans('Changes'))
                    ->visible(
                        fn (Activity $record): bool => ! ActivityLogEvent::isApiPayload($record->event) && (
                                $record
                                    ->properties
                                    ?->hasAny('old', 'attributes') ?? false
                            )
                    )
                    ->schema([

                        Infolists\Components\KeyValueEntry::make('old')
                            ->translateLabel()
                            ->inlineLabel(false)
                            ->state(self::changes('old')),

                        Infolists\Components\KeyValueEntry::make('new')
                            ->translateLabel()
                            ->inlineLabel(false)
                            ->state(self::changes('attributes')),
                    ]),

                Infolists\Components\Fieldset::make('others')
                    ->hiddenLabel()
                    ->schema([
                        Infolists\Components\TextEntry::make('event')
                            ->translateLabel()
                            ->badge()
                            ->placeholder('--'),

                        Infolists\Components\TextEntry::make('log_name')
                            ->translateLabel()
                            ->badge(),

                        Infolists\Components\TextEntry::make('batch_uuid')
                            ->translateLabel()
                            ->placeholder('--'),
                    ]),

            ])
            ->columns(1)
            ->inlineLabel();
    }

    private static function changes(string $type): \Closure
    {
        return function (Activity $record) use ($type) {
            $newProperties = $record->properties
                ?->only($type)
                ->first();

            if ($newProperties === null) {
                return ['' => ''];
            }

            $return = [];

            foreach ($newProperties as $property => $value) {

                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $return[$property] = $value;
            }

            return $return;
        };
    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->translateLabel()
                    ->wrap()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->translateLabel()
                    ->wrap()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('event')
                    ->translateLabel()
                    ->wrap()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->translateLabel()
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('batch_uuid')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true)
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->sortable()
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->translateLabel(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->translateLabel()
                    ->multiple()
                    ->options(ActivityLogName::class),
                Tables\Filters\SelectFilter::make('event')
                    ->translateLabel()
                    ->multiple()
                    ->options(ActivityLogEvent::class),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->translateLabel()
                    ->multiple()
                    ->options(ActivitySubjectType::class),
                Tables\Filters\TernaryFilter::make('has_batch_uuid')
                    ->translateLabel()
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('batch_uuid'),
                        false: fn (Builder $query) => $query->whereNull('batch_uuid'),
                    ),

                DateRangeFilter::make('created_at'),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups(['log_name', 'event']);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ActivityResource\Pages\ListActivities::route('/'),
        ];
    }
}
