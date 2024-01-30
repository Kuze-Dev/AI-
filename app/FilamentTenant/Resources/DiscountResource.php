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
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountLimit;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('eCommerce');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('name')
                        ->label(trans('Name'))
                        ->required()
                        ->maxLength(255),

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
                        ->rules([
                            function ($record) {

                                return function (string $attribute, mixed $value, Closure $fail) use ($record) {
                                    if ($value < $record?->max_uses) {
                                        $fail('The maximum usage must not be less than current. Current is: '.$record->max_uses);
                                    }
                                };
                            },
                        ])

                        ->label(trans('Maximum Usage'))
                        ->helperText(new HtmlString(<<<'HTML'
                                Leave this blank if no maximum usage.
                            HTML)),
                    Placeholder::make('times_used')
                        ->disabled()
                        ->content(fn ($record) => $record = DiscountLimit::whereCode($record?->code)->count()),

                ])
                    ->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Status & Period'))
                        ->schema([
                            Select::make('status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ])->required()
                                ->default('active')
                                ->disablePlaceholderSelection()
                                ->label(trans('Status')),

                            DateTimePicker::make('valid_start_at')
                                ->required()
                                ->label(trans('Start Date')),

                            DateTimePicker::make('valid_end_at')
                                ->after('valid_start_at')
                                ->label(trans('Expiration Date'))
                                ->helperText(new HtmlString(<<<'HTML'
                                        Leave this blank if no expiry.
                                    HTML)),
                        ]),
                ])
                    ->columnSpan(['lg' => 1]),
                Group::make([
                    Section::make(trans('Discount Type'))
                        ->schema([
                            Radio::make('discountCondition.discount_type')->options([
                                'order_sub_total' => 'Order Sub Total',
                                'delivery_fee' => 'Delivery Fee',
                            ])
                                ->required()
                                ->default('order_sub_total')
                                ->formatStateUsing(fn ($record) => $record?->discountCondition()->withTrashed()->first()?->discount_type)
                                ->label(trans('Discount Type')),

                            Radio::make('discountCondition.amount_type')->options([
                                'fixed_value' => 'Fixed Value',
                                'percentage' => 'Percentage',
                            ])
                                ->reactive()
                                ->required()
                                ->default('fixed_value')
                                ->filled()
                                ->formatStateUsing(fn ($record) => $record?->discountCondition()->withTrashed()->first()?->amount_type)
                                ->label(trans('Amount Type')),

                            TextInput::make('discountCondition.amount')
                                ->required()
                                ->mask(fn (Mask $mask) => $mask->money(
                                    prefix: Currency::whereEnabled(true)->value('symbol'),
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
                                ->minValue(1)
                                ->rules(['max:100'], fn (\Filament\Forms\Get $get) => $get('discountCondition.amount_type') === 'percentage')
                                ->formatStateUsing(fn ($record) => $record?->discountCondition()->withTrashed()->first()?->amount)
                                ->label(trans('Discount Amount')),
                        ]),

                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Requirements'))
                        ->schema([
                            // Select::make('discountRequirement.requirement_type')
                            //     ->options([
                            //         'minimum_order_amount' => 'Minimum Purchase Amount',
                            //     ])
                            //     // ->reactive()
                            //     ->formatStateUsing(fn ($record) => $record?->discountRequirement?->requirement_type),

                            TextInput::make('discountRequirement.minimum_amount')
                                ->label(trans('Minimum purchase amount'))
                                ->mask(fn (Mask $mask) => $mask->money(
                                    prefix: Currency::whereEnabled(true)->value('symbol'),
                                    thousandsSeparator: ',',
                                    decimalPlaces: 2,
                                    isSigned: false
                                ))
                                ->formatStateUsing(fn ($record) => $record?->discountRequirement?->minimum_amount)
                                ->helperText(new HtmlString(<<<'HTML'
                                        Leave this blank if no minimum purchase amount.
                                    HTML))
                                ->minValue(1),
                        ]),

                ])->columnSpan(['lg' => 2]),
            ])->columns(3);
    }

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
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->date('F j, Y, g:i a')
                    ->label(trans('Start Date')),

                TextColumn::make('valid_end_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->date('F j, Y, g:i a')
                    ->placeholder('No expiry')
                    ->label(trans('Expiration Date')),

                BadgeColumn::make('status')
                    ->colors([

                        'success' => DiscountStatus::ACTIVE->value,
                        'warning' => DiscountStatus::INACTIVE->value,

                    ])->formatStateUsing(fn (DiscountStatus $state): string => trans(ucfirst($state->value)))->weight('bold'),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label(trans('Deleted Records')),
                SelectFilter::make('status')
                    ->label(trans('Status'))
                    ->options([
                        DiscountStatus::ACTIVE->value => 'Active',
                        DiscountStatus::INACTIVE->value => 'Inactive',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->authorize('update'),
                    ForceDeleteAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(ForceDeleteDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        })
                        ->authorize('forceDelete'),

                    DeleteAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(SoftDeleteDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        })
                        ->authorize('delete'),

                    RestoreAction::make()
                        ->using(function (Discount $record) {
                            try {
                                return app(RestoreDiscountAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        })
                        ->authorize('restore'),
                ]),

            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    /** @return Builder<\Domain\Discount\Models\Discount> */
    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
            ->with(['discountCondition', 'discountRequirement'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscounts::route('/'),
            'create' => CreateDiscount::route('/create'),
            'edit' => EditDiscount::route('/{record}/edit'),
        ];
    }
}
