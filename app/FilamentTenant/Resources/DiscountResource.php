<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\EditDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\ListDiscounts;
use Closure;
use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Discount\Actions\ForceDeleteDiscountAction;
use Domain\Discount\Actions\RestoreDiscountAction;
use Domain\Discount\Actions\SoftDeleteDiscountAction;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Models\Discount;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Str;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class DiscountResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Discount::class;

    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('name')
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('slug')
                        ->unique(ignoreRecord: true)
                        ->disabled(),

                    RichEditor::make('description')
                        ->translateLabel(),

                    TextInput::make('code')
                        ->suffixAction(
                            fn (?string $state): Action => Action::make('code')
                                ->icon('heroicon-o-cog')
                                ->action(fn (TextInput $component) => $component->state((new AutoGenerateCode())()))
                                ->tooltip(trans('auto generate code')),
                        )->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('max_uses')
                        ->numeric()
                        ->label(trans('Maximum Usage'))
                        ->helperText(new HtmlString(<<<HTML
                                Leave this blank if no maximum usage.
                            HTML)),
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
                                ->helperText(new HtmlString(<<<HTML
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
                                ->formatStateUsing(fn ($record) => $record?->discountCondition->discount_type)
                                ->label(trans('Discount Type')),

                            Radio::make('discountCondition.amount_type')->options([
                                'fixed_value' => 'Fixed Value',
                                'percentage' => 'Percentage',
                            ])
                                ->reactive()
                                ->required()
                                ->default('fixed_value')
                                ->filled()
                                ->formatStateUsing(fn ($record) => $record?->discountCondition->amount_type)
                                ->label(trans('Amount Type')),

                            TextInput::make('discountCondition.amount')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->rules(['max:100'], fn (Closure $get) => $get('discountCondition.amount_type') === 'percentage')
                                ->formatStateUsing(fn ($record) => $record?->discountCondition->amount)
                                ->label(trans('Discount Amount')),
                        ]),

                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Requirements'))
                        ->schema([
                            Select::make('discountRequirement.requirement_type')
                                ->options([
                                    'minimum_order_amount' => 'Minimum Purchase Amount',
                                ])
                                ->formatStateUsing(fn ($record) => $record?->discountRequirement->requirement_type),

                            TextInput::make('discountRequirement.minimum_amount')
                                ->label(trans('Minimum purchase amount'))
                                ->numeric()
                                ->formatStateUsing(fn ($record) => $record?->discountRequirement->minimum_amount)
                                ->helperText(new HtmlString(<<<HTML
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
                TextColumn::make('name'),
                TextColumn::make('discountCondition.amount')
                    ->formatStateUsing(function ($record) {
                        return $record?->discountCondition?->amount_type === DiscountAmountType::PERCENTAGE
                            ? (string) $record?->discountCondition?->amount . '%'
                            : ($record?->discountCondition?->amount_type === DiscountAmountType::FIXED_VALUE
                                ? (string) $record?->discountCondition?->amount . 'PHP'
                                : null);
                    })
                    ->label(trans('Amount')),
                TextColumn::make('valid_start_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->date('F j, Y, g:i a')
                    ->label(trans('Start Date')),

                TextColumn::make('valid_end_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->date('F j, Y, g:i a')
                    ->label(trans('Expiration Date')),

                BadgeColumn::make('status')
                    ->colors([

                        'success' => 'active',
                        'warning' => 'inactive',

                    ])->formatStateUsing(fn (string $state): string => __(ucfirst($state)))->weight('bold'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                ForceDeleteAction::make()
                    ->button()
                    ->using(function (Discount $record) {
                        try {
                            return app(ForceDeleteDiscountAction::class)->execute($record);
                        } catch (DeleteRestrictedException $e) {
                            return false;
                        }
                    }),

                DeleteAction::make()
                    ->using(function (Discount $record) {
                        try {
                            return app(SoftDeleteDiscountAction::class)->execute($record);
                        } catch (DeleteRestrictedException $e) {
                            return false;
                        }
                    })
                    ->button(),

                RestoreAction::make()
                    ->using(function (Discount $record) {
                        try {
                            return app(RestoreDiscountAction::class)->execute($record);
                        } catch (DeleteRestrictedException $e) {
                            return false;
                        }
                    })->button(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
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
