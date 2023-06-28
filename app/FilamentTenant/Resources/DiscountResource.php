<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\EditDiscount;
use App\FilamentTenant\Resources\DiscountResource\Pages\ListDiscounts;
use Auth;
use Closure;
use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Models\Discount;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Str;

class DiscountResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Discount::class;

    protected static ?string $navigationGroup = 'SHOP CONFIGURATION';

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
                        ->translateLabel()
                        ->required(),
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

                    // TextInput::make('max_uses_per_user')
                    //     ->required()
                    //     ->numeric()
                    //     ->label(trans('Maximum Usage per Customer')),
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
                                ->timezone(Auth::user()?->timezone)
                                ->label(trans('Start Date')),

                            DateTimePicker::make('valid_end_at')
                                ->timezone(Auth::user()?->timezone)
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
                            Select::make('discountCondition.discount_type')->options([
                                'order_sub_total' => 'Order Sub Total',
                                'delivery_fee' => 'Delivery Fee',
                            ])
                                ->required()
                                ->default('order_sub_total')
                                ->disablePlaceholderSelection()
                                ->label(trans('Discount Type')),
                            Select::make('discountCondition.amount_type')->options([
                                'fixed_value' => 'Fixed Value',
                                'percentage' => 'Percentage',
                            ])
                                ->reactive()
                                ->required()
                                ->default('fixed_value')
                                ->disablePlaceholderSelection()
                                ->label(trans('Discount Type')),

                            TextInput::make('discountCondition.amount')
                                ->required()
                                ->numeric()
                                ->label(trans('Discount Amount')),

                        ]),

                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(trans('Requirements'))
                        ->schema([
                            TextInput::make('discountRequirement.requirement_type')
                                ->disabled()
                                ->default(DiscountRequirementType::MINIMUM_ORDER_AMOUNT),

                            TextInput::make('discountRequirement.minimum_amount')
                                ->label(trans('Minimum purchase amount'))
                                ->numeric()
                                ->helperText(new HtmlString(<<<HTML
                                        Leave this blank if no minimum purchase amount.
                                    HTML))
                                ->rules(['max:100'], fn (Closure $get) => $get('type') === 'percentage'),
                        ]),

                ])->columnSpan(['lg' => 2]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('amount'),
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

                        'primary' => 'active',
                        'danger' => 'inactive',

                    ])->formatStateUsing(fn (string $state): string => __(ucfirst($state)))->weight('bold'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()->button(),
                RestoreAction::make()->button(),
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
