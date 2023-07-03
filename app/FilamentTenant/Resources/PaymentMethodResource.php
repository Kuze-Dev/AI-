<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Features\ECommerce\BankTransfer;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use App\FilamentTenant\Resources\PaymentMethodResource\Pages;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Domain\PaymentMethod\Models\PaymentMethod;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Domain\PaymentMethod\Actions\DeletePaymentMethodAction;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use App\Features\ECommerce\PaypalGateway;
use App\Features\ECommerce\StripeGateway;
use App\Features\ECommerce\OfflineGateway;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Throwable;

class PaymentMethodResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = PaymentMethod::class;

    /** @var string|null */
    protected static ?string $navigationGroup = 'eCommerce';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'gateway'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('title')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    Forms\Components\TextInput::make('subtitle')
                        ->required(),
                    Forms\Components\FileUpload::make('logo')
                        ->formatStateUsing(function ($record) {
                            return $record?->getMedia('logo')
                                ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                ->toArray() ?? [];
                        })
                        ->image()
                        ->beforeStateDehydrated(null)
                        ->dehydrateStateUsing(fn (?array $state) => array_values($state ?? [])[0] ?? null)
                        ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                            $mediaClass = config('media-library.media_model', Media::class);

                            /** @var ?Media $media */
                            $media = $mediaClass::findByUuid($file);

                            if ($component->getVisibility() === 'private') {
                                try {
                                    return $media?->getTemporaryUrl(now()->addMinutes(5));
                                } catch (Throwable $exception) {
                                    // This driver does not support creating temporary URLs.
                                }
                            }

                            return $media?->getUrl();
                        }),
                    Forms\Components\Toggle::make('status')
                        ->inline(false)
                        ->helperText('If enabled, message here')
                        ->reactive(),
                    Forms\Components\Select::make('gateway')
                        ->required()
                        ->options(function () {

                            if (tenancy()->initialized) {

                                $tenant = tenancy()->tenant;

                                return array_filter([
                                    'paypal' => $tenant?->features()->active(app(PaypalGateway::class)->name) ? app(PaypalGateway::class)->label : false,
                                    'stripe' => $tenant?->features()->active(app(StripeGateway::class)->name) ? app(StripeGateway::class)->label : false,
                                    'manual' => $tenant?->features()->active(app(OfflineGateway::class)->name) ? app(OfflineGateway::class)->label : false,
                                    'bank-transfer' => $tenant?->features()->active(app(BankTransfer::class)->name) ? app(BankTransfer::class)->label : false,
                                ], fn ($value) => $value !== false);
                            }

                            return [];

                        })
                        ->reactive(),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(fn (int $value = 250) => $value),

                    Forms\Components\RichEditor::make('instruction'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('gateway')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtitle')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersLayout(Layout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (PaymentMethod $record) {
                            try {
                                return app(DeletePaymentMethodAction::class)->execute($record);
                            } catch (DeleteRestrictedException $e) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /** @return Builder<PaymentMethod> */
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
