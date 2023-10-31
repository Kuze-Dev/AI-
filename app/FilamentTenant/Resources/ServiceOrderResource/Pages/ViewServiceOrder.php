<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\Divider;
use App\FilamentTenant\Support\SchemaFormBuilder;
use App\FilamentTenant\Support\TextLabel;
use App\Settings\ServiceSettings;
use Carbon\Carbon;
use Closure;
use DateTimeZone;
use Domain\Admin\Models\Admin;
use Domain\ServiceOrder\Actions\UpdateServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderStatusUpdatedEvent;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Exceptions\MissingServiceSettingsConfigurationException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceOrderAddress;
use Domain\Taxation\Enums\PriceDisplay;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as ComponentsAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ViewServiceOrder extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    protected static ?string $recordTitleAttribute = 'reference';

    protected function getHeading(): string|Htmlable
    {
        $reference = '';
        if ($this->record instanceof ServiceOrder) {
            $reference = $this->record->reference;
        }

        return trans('Service Order Details #').$reference;
    }

    /**
     * @param  \Domain\ServiceOrder\Models\ServiceOrder  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateServiceOrderAction::class)
            ->execute(
                $record,
                new UpdateServiceOrderData(
                    additional_charges: $data['additional_charges'],
                    customer_form: $data['customer_form'],
                )
            ));
    }

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()
                        ->schema($this->getSection())->columnSpan(2),
                    Section::make(trans('Summary'))->columnSpan(1)->schema([
                        Forms\Components\Group::make()->columns(2)
                            ->schema([
                                BadgeLabel::make(trans('status'))->formatStateUsing(function (string $state): string {
                                    if ($state == ServiceOrderStatus::FORPAYMENT->value) {
                                        return trans('For Payment');
                                    }
                                    if ($state == ServiceOrderStatus::INPROGRESS->value) {
                                        return trans('In Progress');
                                    }

                                    return ucfirst($state);
                                })
                                    ->color(function ($state) {
                                        $newState = str_replace(' ', '_', strtolower($state));

                                        return match ($newState) {
                                            ServiceOrderStatus::PENDING->value, ServiceOrderStatus::INPROGRESS->value => 'warning',
                                            ServiceOrderStatus::CLOSED->value, ServiceOrderStatus::INACTIVE->value, ServiceOrderStatus::CLOSED->value => 'danger',
                                            ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::ACTIVE->value => 'success',
                                            default => 'secondary',
                                        };
                                    })->inline()
                                    ->alignLeft(),
                                self::summaryEditButton(),
                            ]),
                        Forms\Components\Group::make()->columns(2)->schema([
                            TextLabel::make('')
                                ->label(trans('Created By'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record) => $record->admin->first_name.' '.$record->admin->last_name)
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                        ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextLabel::make('')
                                    ->label(trans('Order Date'))
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                TextLabel::make('created_at')
                                    ->alignRight()
                                    ->size('md')
                                    ->inline()
                                    ->formatStateUsing(function ($state) {
                                        /** @var string */
                                        $timeZone = Auth::user()?->timezone;

                                        $formattedState = Carbon::parse($state)
                                            ->setTimezone($timeZone)
                                            ->translatedFormat('F d, Y g:i A');

                                        return $formattedState;
                                    }),
                            ]),
                        Divider::make(''),
                        Forms\Components\Group::make()->columns(2)->schema([
                            TextLabel::make('')
                                ->label(trans('Service Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record) => $record->currency_symbol.' '.number_format($record->service_price, 2, '.', ','))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(trans('Additional Charges'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record, Closure $get) => $record->currency_symbol.' '.number_format(array_reduce($get('additional_charges'), function ($carry, $data) {
                                    if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                                        return $carry + ($data['price'] * $data['quantity']);
                                    }

                                    return $carry;
                                }, 0), 2, '.', ','))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([
                                TextLabel::make('')
                                    ->label(fn ($record) => trans('Tax (').$record->tax_percentage.'%)')
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                TextLabel::make('')
                                    ->label(fn (ServiceOrder $record, Closure $get) => $record->tax_display == PriceDisplay::INCLUSIVE->value ? 'Inclusive'
                                        :
                                        $record->currency_symbol.' '.number_format(self::calculateTaxInfo($record, $get('additional_charges'))->total_price, 2, '.', '.'))
                                    ->alignRight()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                            ])->visible(
                                function (array $state) {
                                    return isset($state['tax_display']);
                                }
                            ),
                            TextLabel::make('')
                                ->label(trans('Total Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                            TextLabel::make('')
                                ->label(fn (ServiceOrder $record, Closure $get) => $record->currency_symbol.' '.
                                    number_format(self::calculateTaxInfo($record, $get('additional_charges'))->total_price, 2, '.', '.'))
                                ->alignRight()
                                ->size('md')
                                ->inline()
                                ->readOnly()
                                ->color('primary'),
                        ]),

                    ]),
                ])->columns(3),
        ];
    }

    private function getSection(): array
    {
        $admin = Admin::first();

        return [
            Section::make(trans('Service'))->schema([
                Grid::make(2)->schema([
                    Placeholder::make('service')
                        ->content(fn ($record) => $record->service_name),
                    Placeholder::make('service Price')
                        ->content(fn ($record) => $record->currency_symbol.' '.number_format($record->service_price, 2, '.', ',')),
                    Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([
                        Placeholder::make('BillingCycle')
                            ->content(fn ($record) => $record->billing_cycle),
                        Placeholder::make('Due date every')
                            ->content(fn ($record) => $record->due_date_every),
                    ])->visible(fn ($record) => $record->service->is_subscription),
                    Forms\Components\Group::make()->columns(2)->columnSpan(2)->schema([
                        Placeholder::make('schedule')
                            ->content(function ($record) use ($admin) {
                                if ($admin) {
                                    return Carbon::parse($record->schedule)
                                        ->timezone(new DateTimeZone($admin->timezone))
                                        ->format('F j Y g:i A');
                                } else {
                                    return 'No admin information available';
                                }
                            }),
                    ])->visible(fn ($record) => ! $record->service->is_subscription),
                ]),
            ]),
            Section::make(trans('Customer'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('first_name')
                            ->content(fn ($record) => $record->customer_first_name),
                        Placeholder::make('last_name')
                            ->content(fn ($record) => $record->customer_last_name),
                        Placeholder::make('email')
                            ->content(fn ($record) => $record->customer_email),
                        Placeholder::make('mobile')
                            ->content(fn ($record) => $record->customer_mobile),
                    ]),
                ]),

            Section::make(trans('Service Address'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->address_line_1 ?? null),
                        Placeholder::make('Country')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->country ?? null),
                        Placeholder::make('State')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->state ?? null),
                        Placeholder::make('City/Province')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->city ?? null),
                        Placeholder::make('Zip Code')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::SERVICE_ADDRESS)->first()->zip_code ?? null),
                    ]),
                ]),

            Section::make(trans('Billing Address'))
                ->schema([
                    Forms\Components\Group::make()->columns(2)->schema([
                        Placeholder::make('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->address_line_1 ?? null),
                        Placeholder::make('Country')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->country ?? null),
                        Placeholder::make('State')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->state ?? null),
                        Placeholder::make('City/Province')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->city ?? null),
                        Placeholder::make('Zip Code')
                            ->content(fn ($record) => ServiceOrderAddress::whereServiceOrderId($record->id)->whereType(ServiceOrderAddressType::BILLING_ADDRESS)->first()->zip_code ?? null),
                    ]),
                ]),
            Group::make()->columns(2)->columnSpan(2)->schema([
                TextLabel::make('')
                    ->label(trans('Additional Charges'))
                    ->alignLeft()
                    ->size('xl')
                    ->weight('bold')
                    ->inline()
                    ->readOnly(),
                Repeater::make('additional_charges')
                    ->label('')
                    ->createItemButtonLabel('Additional Charges')
                    ->columnSpan(2)
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('quantity')->required()->numeric()->reactive()->default(1),
                        TextInput::make('price')->required()->numeric()->reactive(),
                    ])
                    ->maxItems(3)
                    ->columns(3),

            ]),

            Section::make(trans('Service Fill-up Form'))
                ->schema([
                    SchemaFormBuilder::make('customer_form', fn ($record) => $record->service->blueprint->schema)
                        ->schemaData(fn ($record) => $record->service->blueprint->schema),
                ])
                ->hidden(fn (Closure $get) => $get('service_id') === null)
                ->columnSpan(2),
        ];
    }

    private static function summaryEditButton(): Support\ButtonAction
    {
        return Support\ButtonAction::make('Edit')
            ->execute(function (ServiceOrder $record, Closure $get, Closure $set) {
                return Forms\Components\Actions\Action::make(trans('edit'))
                    ->color('primary')
                    ->label('Edit')
                    ->size('sm')
                    ->modalHeading(trans('Edit Status'))
                    ->modalWidth('xl')
                    ->form([
                        Forms\Components\Select::make('status_options')
                            ->label('')
                            ->options(function () use ($record) {
                                $options = [
                                    ServiceOrderStatus::PENDING->value => trans('Pending'),
                                    ServiceOrderStatus::FORPAYMENT->value => trans('For payment'),
                                    ServiceOrderStatus::INPROGRESS->value => trans('In progress'),
                                    ServiceOrderStatus::COMPLETED->value => trans('Completed'),
                                ];
                                if (isset($record->billing_cycle)) {
                                    $options = [
                                        ServiceOrderStatus::PENDING->value => trans('Pending'),
                                        ServiceOrderStatus::FORPAYMENT->value => trans('For payment'),
                                        ServiceOrderStatus::ACTIVE->value => trans('Active'),
                                        ServiceOrderStatus::CLOSED->value => trans('Closed'),
                                    ];
                                }

                                return $options;
                            })
                            ->disablePlaceholderSelection()
                            ->formatStateUsing(function () use ($record) {
                                return $record->status;
                            }),
                        Forms\Components\Toggle::make('send_email')
                            ->label(trans('Send email notification'))
                            ->default(false)
                            ->reactive(),
                    ])
                    ->action(
                        function (
                            array $data,
                            self $livewire,
                            ComponentsAction $action
                        ) use (
                            $record,
                            $set
                        ) {
                            try {
                                DB::transaction(
                                    function (
                                    ) use (
                                        $data,
                                        $livewire,
                                        $action,
                                        $record,
                                        $set
                                    ) {
                                        $shouldNotifyCustomer = $livewire
                                            ->mountedFormComponentActionData['send_email'];

                                        if (
                                            $shouldNotifyCustomer &&
                                            empty(app(ServiceSettings::class)->email_sender_name)
                                        ) {
                                            throw new MissingServiceSettingsConfigurationException(
                                                'Email sender not found, please update your service settings'
                                            );
                                        }

                                        $shouldSendEmailToAdmin = app(ServiceSettings::class)
                                            ->admin_should_receive;

                                        $adminEmailReceiver = app(ServiceSettings::class)
                                            ->admin_main_receiver;

                                        if (
                                            $shouldSendEmailToAdmin &&
                                            empty($adminEmailReceiver)
                                        ) {
                                            throw new MissingServiceSettingsConfigurationException(
                                                'Email receiver not found, please update your service settings'
                                            );
                                        }

                                        $status = $data['status_options'];

                                        $updateData = ['status' => $status];

                                        if ($record->update($updateData)) {
                                            event(new AdminServiceOrderStatusUpdatedEvent(
                                                /** @phpstan-ignore-next-line */
                                                customer: $record->customer,
                                                serviceOrder: $record,
                                                shouldNotifyCustomer: $shouldNotifyCustomer
                                            ));

                                            $set('status', ucfirst(str_replace('-', ' ', $status)));

                                            $action->successNotificationTitle(
                                                trans('Service Order updated successfully')
                                            )->success();
                                        }
                                    }
                                );
                            } catch (MissingServiceSettingsConfigurationException $m) {
                                $action->failureNotificationTitle(trans($m->getMessage()))
                                    ->failure();

                                report($m);
                            } catch (InvalidServiceBillException $i) {
                                $action->failureNotificationTitle(trans('No service bill found'))
                                    ->failure();

                                report($i);
                            } catch (Exception $e) {
                                $action
                                    ->failureNotificationTitle(trans('Something went wrong!'))
                                    ->failure();

                                report($e);
                            }
                        }
                    )
                    ->withActivityLog();
            })
            ->disableLabel()
            ->columnSpan(1)
            ->alignRight()
            ->size('sm')
            ->hidden(function (ServiceOrder $record) {
                return $record->status == ServiceOrderStatus::FORPAYMENT ||
                    $record->status == ServiceOrderStatus::COMPLETED;
            });
    }

    public static function calculateTaxInfo(ServiceOrder $record, array $additionalCharges): ServiceOrderTaxData
    {
        $subTotal = ServiceOrderResource::getSubtotal($record->service_price, $additionalCharges);
        $totalPrice = $subTotal;
        $taxTotal = 0;
        if ($record->tax_display === PriceDisplay::EXCLUSIVE->value) {
            $taxTotal = $subTotal * ($record->tax_percentage / 100.0);
            $totalPrice = $subTotal + $taxTotal;
        }

        return new ServiceOrderTaxData(
            sub_total: $subTotal,
            tax_display: $record->tax_display,
            tax_percentage: $record->tax_percentage,
            tax_total: $taxTotal,
            total_price: $totalPrice
        );
    }
}
