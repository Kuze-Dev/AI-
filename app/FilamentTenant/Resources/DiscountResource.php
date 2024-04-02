<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\EditDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\ListDiscounts;
use Closure;
use Domain\Currency\Models\Currency;
use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Discount\Actions\ForceDeleteDiscountAction;
use Domain\Discount\Actions\RestoreDiscountAction;
use Domain\Discount\Actions\SoftDeleteDiscountAction;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->label(trans('Name'))
                        ->required()
                        ->maxLength(255),

                    Hidden::make('slug')
                        ->dehydrateStateUsing(fn (Get $get) => Str::slug($get('name'))),

                    RichEditor::make('description')
                        ->label(trans('Description'))
                        ->translateLabel()
                        ->maxLength(255),

                    TextInput::make('code')
                        ->label(trans('Code'))
                        ->suffixAction(
                            fn (?string $state): Action => Action::make('code')
                                ->icon('heroicon-o-cog')
                                ->action(fn (TextInput $component) => $component->state((new AutoGenerateCode())()))
                                ->tooltip(trans('auto generate code')),
                        )->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),

                    TextInput::make('max_uses')
                        ->numeric()
                        ->rule(
                            fn ($record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                                if ($value < $record?->max_uses) {
                                    $fail('The maximum usage must not be less than current. Current is: '.$record->max_uses);
                                }
                            },
                        )

                        ->label(trans('Maximum Usage'))
                        ->helperText(trans('Leave this blank if no maximum usage.')),
                    Placeholder::make('times_used')
                        ->disabled()
                        ->content(fn (?Discount $record) => DiscountLimit::whereCode($record?->code)->count()),

                ])
                    ->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Status & Period'))
                        ->schema([
                            Select::make('status')
                                ->translateLabel()
                                ->options(DiscountStatus::class)
                                ->required()
                                ->enum(DiscountStatus::class)
                                ->default(DiscountStatus::ACTIVE)
                                ->selectablePlaceholder(),

                            DateTimePicker::make('valid_start_at')
                                ->required()
                                ->before('valid_end_at')
                                ->label(trans('Start Date')),

                            DateTimePicker::make('valid_end_at')
                                ->after('valid_start_at')
                                ->label(trans('Expiration Date'))
                                ->helperText(trans('Leave this blank if no expiry.')),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),
                Group::make([
                    Section::make(trans('Discount Type'))
                        ->relationship('discountCondition')
                        ->schema([
                            Radio::make('discount_type')
                                ->translateLabel()
                                ->required()
                                ->options(DiscountConditionType::class)
                                ->enum(DiscountConditionType::class)
                                ->default(DiscountConditionType::ORDER_SUB_TOTAL),

                            Radio::make('amount_type')
                                ->translateLabel()
                                ->required()
                                ->options(DiscountAmountType::class)
                                ->enum(DiscountAmountType::class)
                                ->default(DiscountAmountType::FIXED_VALUE)
                                ->filled()
                                ->label(trans('Amount Type'))
                                ->reactive(),

                            TextInput::make('amount')
                                ->label(trans('Discount Amount'))
                                ->required()
//                                ->mask(fn (Mask $mask) => $mask->money(
//                                    prefix: Currency::whereEnabled(true)->value('symbol'),
//                                    thousandsSeparator: ',',
//                                    decimalPlaces: 2,
//                                    isSigned: false
//                                ))
                                ->minValue(1)
                                ->numeric()
                                ->rule(
                                    'max:100',
                                    fn (Get $get) => $get('amount_type') === DiscountAmountType::PERCENTAGE
                                ),
                        ]),

                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Requirements'))
                        ->relationship('discountRequirement')
                        ->schema([

                            Hidden::make('requirement_type')
                                ->dehydrateStateUsing(fn () => DiscountRequirementType::MINIMUM_ORDER_AMOUNT),

                            TextInput::make('minimum_amount')
                                ->label(trans('Minimum purchase amount'))
//                                ->mask(fn (Mask $mask) => $mask->money(
//                                    prefix: Currency::whereEnabled(true)->value('symbol'),
//                                    thousandsSeparator: ',',
//                                    decimalPlaces: 2,
//                                    isSigned: false
//                                ))
                                ->numeric()
                                ->helperText(trans('Leave this blank if no minimum purchase amount.'))
                                ->minValue(1),
                        ]),

                ])->columnSpan(['lg' => 2]),
            ])->columns(3);
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label(trans('Name')),
                TextColumn::make('discountCondition.discount_type')
                    ->label(trans('Discount Type'))
                    ->formatStateUsing(function ($record) {

                        $discountType = $record?->discountCondition()->withTrashed()->first()?->discount_type;

                        $label = '';
                        if ($discountType === DiscountConditionType::ORDER_SUB_TOTAL) {
                            $label = trans('Order Sub Total');
                        }

                        if ($discountType === DiscountConditionType::DELIVERY_FEE) {
                            $label = trans('Shipping Fee');
                        }

                        return $label;
                    }),
                TextColumn::make('discountCondition.amount')
                    ->formatStateUsing(function ($record) {
                        $discountCondition = $record?->discountCondition()->withTrashed()->first();
                        if ($discountCondition->amount_type === DiscountAmountType::PERCENTAGE) {
                            return (string) $discountCondition->amount.'%';
                        } elseif ($discountCondition->amount_type === DiscountAmountType::FIXED_VALUE) {
                            return Currency::whereEnabled(true)->value('symbol').' '.(string) $discountCondition->amount;
                        }

                        return null;
                    })
                    ->label(trans('Amount')),
                TextColumn::make('valid_start_at')
                    ->dateTime()
                    ->date('F j, Y, g:i a')
                    ->label(trans('Start Date')),

                TextColumn::make('valid_end_at')
                    ->dateTime()
                    ->date('F j, Y, g:i a')
                    ->placeholder('No expiry')
                    ->label(trans('Expiration Date')),

                TextColumn::make('status')
                    ->badge()
                    ->weight('bold'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label(trans('Deleted Records')),
                SelectFilter::make('status')
                    ->label(trans('Status'))
                    ->options([
                        DiscountStatus::ACTIVE->value => DiscountStatus::ACTIVE->getLabel(),
                        DiscountStatus::INACTIVE->value => DiscountStatus::INACTIVE->getLabel(),
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->grouped(),
                    ForceDeleteAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(ForceDeleteDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),

                    DeleteAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(SoftDeleteDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),

                    RestoreAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(RestoreDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),

            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    /** @return Builder<\Domain\Discount\Models\Discount> */
    #[\Override]
    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
            ->with(['discountCondition', 'discountRequirement'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListDiscounts::route('/'),
            'create' => CreateDiscount::route('/create'),
            'edit' => EditDiscount::route('/{record}/edit'),
        ];
    }
}
