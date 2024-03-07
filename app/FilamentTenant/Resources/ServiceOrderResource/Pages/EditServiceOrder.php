<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use Akaunting\Money\Money;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use App\FilamentTenant\Resources\ServiceOrderResource\Support as ServiceOrderSupport;
use App\FilamentTenant\Support\ButtonAction;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\Settings\ServiceSettings;
use Closure;
use Domain\Payments\Enums\PaymentRemark;
use Domain\ServiceOrder\Actions\GenerateMilestonePipelineAction;
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\Actions\ServiceOrderBankTransferAction;
use Domain\ServiceOrder\Actions\UpdateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceBillData;
use Domain\ServiceOrder\Enums\PaymentPlanType;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderStatusUpdatedEvent;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Exceptions\MissingServiceSettingsConfigurationException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceOrderAddress;
use Domain\Taxation\Enums\PriceDisplay;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * @property-read \Domain\ServiceOrder\Models\ServiceOrder $record
 */
class EditServiceOrder extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected static ?string $recordTitleAttribute = 'reference';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return trans('Service Order Details #:service-order', ['service-order' => $this->record->reference]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $amountInfo = app(GetTaxableInfoAction::class)
            ->computeTotalPriceWithTax(
                ServiceOrderSupport::getSubtotal($this->record->service_price, $data['additional_charges']),
                $this->record
            );

        /**
         * 'additional_charges' => $data['additional_charges'],
         * 'customer_form' => $data['customer_form'],
         */
        return $data + [
            'sub_total' => $amountInfo->sub_total,
            'tax_total' => $amountInfo->tax_total,
            'total_price' => $amountInfo->total_price,
        ];
    }

    protected function afterSave(): void
    {
        $serviceBill = $this->record->serviceBills()->first();

        if ($serviceBill && ! $this->record->is_subscription) {
            app(UpdateServiceBillAction::class)->execute($serviceBill, new UpdateServiceBillData(
                sub_total: $this->record->sub_total,
                tax_total: $this->record->tax_total,
                total_amount: $this->record->total_price,
                additional_charges: $this->record->additional_charges,
            ));
        }
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Forms\Components\Group::make()
                ->schema([

                    Forms\Components\Section::make(trans('Service'))
                        ->schema([
                            Forms\Components\Placeholder::make(trans('Service'))
                                ->content(fn (ServiceOrder $record) => $record->service_name),

                            Forms\Components\Placeholder::make(trans('Service price'))
                                ->content(fn (ServiceOrder $record) => $record->format_service_price_for_display),

                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Placeholder::make(trans('Billing cycle'))
                                        ->content(fn (ServiceOrder $record) => $record->billing_cycle?->getLabel()),

                                    Forms\Components\Placeholder::make(trans('Due date every'))
                                        ->content(
                                            fn (ServiceOrder $record) => Str::of('? ? after billing date')
                                                ->replaceArray('?', [
                                                    (string) $record->due_date_every,
                                                    $record->due_date_every > 1 ? 'days' : 'day',
                                                ])
                                        ),
                                ])
                                ->visible(fn (ServiceOrder $record) => $record->service?->is_subscription)
                                ->columns(2)
                                ->columnSpan(2),

                            Forms\Components\Placeholder::make(trans('Schedule'))
                                ->columnSpan(2)
                                ->content(
                                    fn (ServiceOrder $record) => $record->schedule
                                        ->timezone(Filament::auth()->user()->timezone)
                                        ->format('F j Y g:i A')
                                )
                                ->visible(fn (ServiceOrder $record) => ! $record->service->is_subscription),

                            Forms\Components\Group::make()
                                ->columnSpan(2)
                                ->visible(fn (ServiceOrder $record) => $record->payment_type === PaymentPlanType::MILESTONE)
                                ->schema([

                                    Forms\Components\Repeater::make('payment_plan')
                                        ->translateLabel()
                                        ->columnSpan(2)
                                        ->reactive()
                                        ->itemLabel(function ($uuid, $component) {
                                            $keys = array_keys($component->getState());
                                            $index = array_search($uuid, $keys);

                                            return $index + 1;
                                        })
                                        ->columns(2)
                                        ->disabled()
                                        ->schema([
                                            Forms\Components\TextInput::make('description')
                                                ->translateLabel()
                                                ->required(),

                                            Forms\Components\TextInput::make('amount')
                                                ->label(fn (ServiceOrder $record) => $record->payment_value?->getLabel())
                                                ->required()
                                                ->hintAction(
                                                    Forms\Components\Actions\Action::make('generate')
                                                        ->translateLabel()
                                                        ->requiresConfirmation()
                                                        ->disabled(function (ServiceOrder $record, Get $get) {

                                                            if (is_null($record->payment_plan)) {
                                                                return true;
                                                            }

                                                            $key = array_search($get('description'), array_column($record->payment_plan, 'description'));

                                                            if ($key !== false) {
                                                                return $record->payment_plan[$key]['is_generated'];
                                                            }

                                                            return true;
                                                        })
                                                        ->successNotificationTitle(trans('Milestone generated successfully'))
                                                        ->action(function (Forms\Components\Actions\Action $action, ServiceOrder $record, int|float $state, Get $get): void {

                                                            app(GenerateMilestonePipelineAction::class)
                                                                ->execute(
                                                                    new ServiceBillMilestonePipelineData(
                                                                        $record,
                                                                        [
                                                                            'description' => $get('description'),
                                                                            'amount' => $state,
                                                                        ]
                                                                    )
                                                                );

                                                            $action->success();
                                                        }),
                                                ),

                                        ]),

                                ]),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Customer'))
                        ->schema([
                            Forms\Components\Placeholder::make('first_name')
                                ->content(fn (ServiceOrder $record) => $record->customer_first_name),

                            Forms\Components\Placeholder::make('last_name')
                                ->content(fn (ServiceOrder $record) => $record->customer_last_name),

                            Forms\Components\Placeholder::make('email')
                                ->content(fn (ServiceOrder $record) => $record->customer_email),

                            Forms\Components\Placeholder::make('mobile')
                                ->content(fn (ServiceOrder $record) => $record->customer_mobile),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Service Address'))
                        ->relationship('serviceOrderServiceAddress')
                        ->schema([
                            Forms\Components\Placeholder::make('address_line_1')
                                ->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->address_line_1),

                            Forms\Components\Placeholder::make('country')
                                ->content(fn (ServiceOrderAddress $record) => $record->country),

                            Forms\Components\Placeholder::make('state')
                                ->content(fn (ServiceOrderAddress $record) => $record->state),

                            Forms\Components\Placeholder::make('city')
                                ->label('City/Province')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->city),

                            Forms\Components\Placeholder::make('zip_code')
                                ->label('Zip Code')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->zip_code),

                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Billing Address'))
                        ->visible(fn (ServiceOrder $record) => $record->serviceOrderBillingAddress === null)
                        ->schema([
                            Forms\Components\Placeholder::make('same_as_billing_address')
                                ->hiddenLabel()
                                ->content(fn () => trans('Same as Service Address')),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Billing Address'))
                        ->relationship('serviceOrderBillingAddress')
                        ->visible(fn (ServiceOrder $record) => $record->serviceOrderBillingAddress !== null)
                        ->schema([

                            Forms\Components\Placeholder::make('address_line_1')
                                ->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->address_line_1),

                            Forms\Components\Placeholder::make('country')
                                ->content(fn (ServiceOrderAddress $record) => $record->country),

                            Forms\Components\Placeholder::make('state')
                                ->content(fn (ServiceOrderAddress $record) => $record->state),

                            Forms\Components\Placeholder::make('city')
                                ->label('City/Province')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->city),

                            Forms\Components\Placeholder::make('zip_code')
                                ->label('Zip Code')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->zip_code),

                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Additional Charges'))
                        ->schema([
                            Forms\Components\Repeater::make('additional_charges')
                                ->hiddenLabel()
                                ->columnSpan(2)
                                ->defaultItems(0)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->required()
                                        ->numeric()
                                        ->reactive()
                                        ->default(1),

                                    Forms\Components\DateTimePicker::make('date')
                                        ->minDate(now())
                                        ->seconds(false)
                                        ->default(now())
                                        ->disabled()
                                        ->hidden(),

                                    Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->numeric()
                                        ->reactive(),
                                ])
                                ->columns(3),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make(trans('Service Fill-up Form'))
                        ->schema([
                            SchemaFormBuilder::make('customer_form', fn ($record) => $record->service->blueprint->schema)
                                ->schemaData(fn ($record) => $record->service->blueprint->schema),
                        ])
                        ->hidden(fn (Get $get) => $get('service_id') === null)
                        ->columns(2),

                ])
                ->columnSpan(2),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make(trans('Service order summary'))
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([

                                    Forms\Components\Placeholder::make(trans('Status'))
                                        ->hiddenLabel()
//                                       ->color(fn (ServiceOrder $record) => $record->format_status_for_display)
                                        ->content(fn (ServiceOrder $record) => $record->format_status_for_display),

                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('edit_status')
                                            ->label(trans('Edit'))
                                            ->modalHeading(trans('Edit Status'))
                                            ->modalWidth('xl')
                                            ->hidden(function (ServiceOrder $record) {
                                                return $record->status === ServiceOrderStatus::FORPAYMENT ||
                                                    $record->status === ServiceOrderStatus::COMPLETED;
                                            })
                                            ->withActivityLog()
                                            ->form([

                                                Forms\Components\Select::make('status')
                                                    ->hiddenLabel()
                                                    ->options(fn (ServiceOrder $record) => ServiceOrderStatus::casesForServiceOrder($record))
                                                    ->selectablePlaceholder(false)
                                                    ->required()
                                                    ->default(function (ServiceOrder $record) {
                                                        return $record->status;
                                                    }),

                                                Forms\Components\Toggle::make('is_send_email')
                                                    ->label(trans('Send email notification'))
                                                    ->default(false)
                                                    ->reactive(),
                                            ])
                                            ->action(function (Forms\Components\Actions\Action $action, array $data, ServiceOrder $record) {

                                                $setting = app(ServiceSettings::class);

                                                if (
                                                    $data['is_send_email'] &&
                                                    empty($setting->email_sender_name)
                                                ) {
                                                    $action->failureNotificationTitle(
                                                        trans('Email sender not found, please update your service settings')
                                                    )
                                                        ->failure();
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                }

                                                if ($setting->admin_should_receive && empty($setting->admin_main_receiver)) {
                                                    $action->failureNotificationTitle(
                                                        trans('Email receiver not found, please update your service settings')
                                                    )
                                                        ->failure();
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                }

                                                try {
                                                    if (
                                                        $record->update([
                                                            'status' => $data['status'],
                                                        ])
                                                    ) {
                                                        event(new AdminServiceOrderStatusUpdatedEvent(
                                                            serviceOrder: $record,
                                                            shouldNotifyCustomer: $data['is_send_email']
                                                        ));

                                                        $action->successNotificationTitle(
                                                            trans('Service order updated successfully')
                                                        )
                                                            ->success();
                                                    }
                                                } catch (MissingServiceSettingsConfigurationException $e) {
                                                    $action->failureNotificationTitle(trans($e->getMessage()))
                                                        ->failure();

                                                    report($e);
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                } catch (InvalidServiceBillException $e) {
                                                    $action->failureNotificationTitle(trans('No service bill found'))
                                                        ->failure();

                                                    report($e);
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                } catch (ModelNotFoundException $e) {
                                                    $action->failureNotificationTitle(trans($e->getMessage()))
                                                        ->failure();

                                                    report($e);
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                } catch (Exception $e) {
                                                    $action->failureNotificationTitle(trans('Something went wrong!'))
                                                        ->failure();

                                                    report($e);
                                                    $action->halt(shouldRollBackDatabaseTransaction: true);
                                                }
                                            }),
                                    ]),
                                ])
                                ->columns(2),

                            Forms\Components\Placeholder::make(trans('Created By'))
                                ->inlineLabel()
                                ->content(fn (ServiceOrder $record) => $record->admin?->full_name),

                            Forms\Components\Placeholder::make(trans('Order Date'))
                                ->inlineLabel()
                                ->content(
                                    fn (ServiceOrder $record) => $record->created_at
                                        ?->timezone(Auth::user()?->timezone ?? config('domain.admin.default_timezone'))
                                        ->translatedFormat('F d, Y g:i A')
                                ),

                            //                                    self::ProofOfPaymentButton(),
                            //                                    Divider::make(''),

                            Forms\Components\Placeholder::make(trans('Service Price'))
                                ->inlineLabel()
                                ->content(fn (ServiceOrder $record) => $record->format_service_price_for_display),

                            Forms\Components\Placeholder::make(trans('Additional Charges'))
                                ->inlineLabel()
                                ->content(fn (ServiceOrder $record) => money(
                                    ServiceOrderSupport::getSubtotal(0, $record->additional_charges) * 100,
                                    $record->currency_code
                                )),

                            Forms\Components\Placeholder::make('format_tax_percentage_for_display')
                                ->label(fn (ServiceOrder $record) => trans($record->format_tax_percentage_for_display))
                                ->visible(
                                    fn (ServiceOrder $record): bool => $record->tax_display === PriceDisplay::EXCLUSIVE
                                )
                                ->inlineLabel()
                                ->content(function (ServiceOrder $record) {

                                    if ($record->tax_display == PriceDisplay::INCLUSIVE) {
                                        return $record->format_tax_for_display;
                                    }

                                    $result = app(GetTaxableInfoAction::class)->computeTotalPriceWithTax(
                                        ServiceOrderSupport::getSubtotal($record->service_price, $record->additional_charges),
                                        $record
                                    );

                                    return money($result->tax_total * 100, $record->currency_code);
                                }),

                            Forms\Components\Placeholder::make(trans('Total Price'))
                                ->inlineLabel()
                                ->content(function (ServiceOrder $record): Money {
                                    $result = app(GetTaxableInfoAction::class)->computeTotalPriceWithTax(
                                        ServiceOrderSupport::getSubtotal($record->service_price, $record->additional_charges),
                                        $record
                                    );

                                    return money($result->total_price * 100,
                                        $record->currency_code
                                    );
                                }),

                        ]),

                    Forms\Components\Section::make(trans('Bills summary'))
                        ->schema([

                            Forms\Components\Placeholder::make(trans('Unpaid Bills'))
                                ->inlineLabel()
                                ->content(fn (ServiceOrder $record) => $record->totalUnpaidBills()),

                            Forms\Components\Placeholder::make(trans('Unpaid Amount'))
                                ->inlineLabel()
                                ->content(fn (ServiceOrder $record) => $record->totalBalance()),

                        ]),
                ])
                ->columnSpan(1),

        ])
            ->columns(3);
    }

    private static function ProofOfPaymentButton(): ButtonAction
    {
        return ButtonAction::make('proof_of_payment')
            ->disableLabel()
            ->execute(function (ServiceOrder $record, Set $set) {
                $footerActions = self::showProofOfPaymentActions($record, $set);

                return $footerActions;
            })
            ->fullWidth()
            ->size('md')
            ->hidden(function (ServiceOrder $record) {

                if ($record->status === ServiceOrderStatus::FOR_APPROVAL) {
                    return false;
                }

                return true;
            });
    }

    private static function showProofOfPaymentActions(ServiceOrder $record, Closure $set): Forms\Components\Actions\Action
    {
        $order = $record;

        return Forms\Components\Actions\Action::make('proof_of_payment')
            ->color('gray')
            ->label(trans('View Proof of payment'))
            ->size('sm')
            ->action(function (array $data) use ($record, $set) {
                app(ServiceOrderBankTransferAction::class)->execute($data, $record, $set);
            })
            ->modalHeading(trans('Proof of Payment'))
            ->modalWidth('lg')
            ->form([
                Forms\Components\Textarea::make('customer_message')
                    ->label(trans('Customer Message'))
                    ->formatStateUsing(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return $payment->customer_message;
                    })->disabled(),
                Forms\Components\FileUpload::make('bank_proof_image')
                    ->label(trans('Customer Upload'))
                    ->formatStateUsing(function () use ($record) {
                        return $record->latestPayment()?->getMedia('image')
                            ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                            ->toArray() ?? [];
                    })
                    ->hidden(function () use ($record) {
                        return (bool) (empty($record->latestPayment()?->getFirstMediaUrl('image')));
                    })
                    ->image()
                    ->getUploadedFileUrlUsing(static function (
                        Forms\Components\FileUpload $component,
                        string $file
                    ): ?string {
                        $mediaClass = config('media-library.media_model', Media::class);

                        /** @var ?Media $media */
                        $media = $mediaClass::findByUuid($file);

                        if ($component->getVisibility() === 'private') {
                            try {
                                return $media?->getTemporaryUrl(now()->addMinutes(5));
                            } catch (Throwable) {
                            }
                        }

                        return $media?->getUrl();
                    })->disabled(),
                Forms\Components\Select::make('payment_remarks')
                    ->label('Status')
                    ->required()
                    ->options(
                        collect(PaymentRemark::cases())
                            ->mapWithKeys(fn (PaymentRemark $target) => [$target->value => Str::headline($target->value)])
                            ->toArray()
                    )
                    ->enum(PaymentRemark::class),
                Forms\Components\Textarea::make('message')
                    ->maxLength(255)
                    ->label(trans('Admin Message'))
                    ->formatStateUsing(function () use ($order) {
                        /** @var \Domain\Payments\Models\Payment $payment */
                        $payment = $order->payments->first();

                        return $payment->admin_message;
                    }),
            ])
            ->slideOver()
            ->icon('heroicon-s-eye');
    }
}
