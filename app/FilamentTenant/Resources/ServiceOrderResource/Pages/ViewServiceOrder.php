<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use Akaunting\Money\Money;
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
use Domain\ServiceOrder\Actions\GetTaxableInfoAction;
use Domain\ServiceOrder\Actions\UpdateServiceBillAction;
use Domain\ServiceOrder\Actions\UpdateServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderData;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $amountInfo = self::calculateTaxInfo($record, $data['additional_charges']);

        $serviceOrder = app(UpdateServiceOrderAction::class)->execute(
            $record,
            new UpdateServiceOrderData(
                sub_total: $amountInfo->sub_total,
                tax_total: $amountInfo->tax_total,
                total_price: $amountInfo->total_price,
                additional_charges: $data['additional_charges'],
                customer_form: $data['customer_form'],
            ));

        $serviceBill = $serviceOrder->serviceBills()->first();

        if ($serviceOrder instanceof ServiceOrder && $serviceBill && ! $record->is_subscription) {
            app(UpdateServiceBillAction::class)->execute($serviceBill, new UpdateServiceBillData(
                sub_total: $serviceOrder->sub_total,
                tax_total: $serviceOrder->tax_total,
                total_amount: $serviceOrder->total_price,
                additional_charges: $serviceOrder->additional_charges,
            ));
        }

        return DB::transaction(fn () => $serviceOrder);
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
                        ->schema($this->getSection())
                        ->columnSpan(2),
                    Section::make(trans('Summary'))
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    BadgeLabel::make(trans('status'))
                                        ->formatStateUsing(fn (ServiceOrder $record) => $record->format_status_for_display)
                                        ->color(fn (ServiceOrder $record) => $record->badge_color_for_status_display)
                                        ->inline()
                                        ->alignLeft(),
                                    self::summaryEditButton(),
                                ])
                                ->columns(2),
                            Forms\Components\Group::make()
                                ->schema([
                                    TextLabel::make('')
                                        ->label(trans('Created By'))
                                        ->alignLeft()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly(),
                                    TextLabel::make('')
                                        ->label(fn (ServiceOrder $record) => $record->admin?->full_name)
                                        ->alignRight()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly(),
                                ])
                                ->columns(2),
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
                                        ->formatStateUsing(
                                            function (string $state) {
                                                /** @var string */
                                                $timeZone = Auth::user()?->timezone;

                                                $formattedState = Carbon::parse($state)
                                                    ->setTimezone($timeZone)
                                                    ->translatedFormat('F d, Y g:i A');

                                                return $formattedState;
                                            }
                                        ),
                                ]),
                            Divider::make(''),
                            Forms\Components\Group::make()
                                ->schema([
                                    TextLabel::make('')
                                        ->label(trans('Service Price'))
                                        ->alignLeft()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly(),
                                    TextLabel::make('')
                                        ->label(fn (ServiceOrder $record) => $record->format_service_price_for_display)
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
                                        ->label(
                                            fn (Closure $get) => money(
                                                ServiceOrderResource::getSubtotal(0, $get('additional_charges')) * 100,
                                                $get('currency_code')
                                            )
                                        )
                                        ->alignRight()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly(),
                                    Group::make()
                                        ->schema([
                                            TextLabel::make('')
                                                ->label(fn (ServiceOrder $record) => trans($record->format_tax_percentage_for_display))
                                                ->alignLeft()
                                                ->size('md')
                                                ->inline()
                                                ->readOnly(),
                                            TextLabel::make('')
                                                ->label(
                                                    function (ServiceOrder $record, Closure $get): Money|string {
                                                        return $record->tax_display == PriceDisplay::INCLUSIVE
                                                            ? $record->format_tax_for_display
                                                            : money(
                                                                self::calculateTaxInfo($record, $get('additional_charges'))
                                                                    ->tax_total * 100,
                                                                $record->currency_code
                                                            );
                                                    }
                                                )
                                                ->alignRight()
                                                ->size('md')
                                                ->inline()
                                                ->readOnly(),
                                        ])
                                        ->visible(
                                            fn (ServiceOrder $record): bool => filled($record->tax_display) &&
                                                $record->tax_display == PriceDisplay::EXCLUSIVE
                                        )
                                        ->columns(2)
                                        ->columnSpan(2),
                                    TextLabel::make('')
                                        ->label(trans('Total Price'))
                                        ->alignLeft()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly()
                                        ->color('primary'),
                                    TextLabel::make('')
                                        ->label(
                                            fn (ServiceOrder $record, Closure $get): Money => money(
                                                self::calculateTaxInfo($record, $get('additional_charges'))
                                                    ->total_price * 100,
                                                $record->currency_code
                                            )
                                        )
                                        ->alignRight()
                                        ->size('md')
                                        ->inline()
                                        ->readOnly()
                                        ->color('primary'),
                                ])
                                ->columns(2),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3),
        ];
    }

    private function getSection(): array
    {
        $admin = Admin::first();

        return [
            Section::make(trans('Service'))
                ->schema([
                    Placeholder::make('service')
                        ->content(fn (ServiceOrder $record) => $record->service_name),
                    Placeholder::make('service Price')
                        ->content(fn (ServiceOrder $record) => $record->format_service_price_for_display),
                    Group::make()
                        ->schema([
                            Placeholder::make('BillingCycle')
                                ->content(fn (ServiceOrder $record) => ucfirst((string) $record->billing_cycle?->value)),
                            Placeholder::make('Due date every')
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
                    Placeholder::make('schedule')
                        ->content(
                            fn (ServiceOrder $record) => $admin
                                ? Carbon::parse($record->schedule)
                                    ->timezone(new DateTimeZone($admin->timezone))
                                    ->format('F j Y g:i A')
                                : trans('No admin information available')
                        )
                        ->visible(fn ($record) => ! $record->service->is_subscription),
                ])
                ->columns(2),
            Section::make(trans('Customer'))
                ->schema([
                    Placeholder::make('first_name')
                        ->content(fn (ServiceOrder $record) => $record->customer_first_name),
                    Placeholder::make('last_name')
                        ->content(fn (ServiceOrder $record) => $record->customer_last_name),
                    Placeholder::make('email')
                        ->content(fn (ServiceOrder $record) => $record->customer_email),
                    Placeholder::make('mobile')
                        ->content(fn (ServiceOrder $record) => $record->customer_mobile),
                ])
                ->columns(2),
            Section::make(trans('Service Address'))
                ->relationship('serviceOrderServiceAddress')
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Placeholder::make('address_line_1')
                                ->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->address_line_1),
                            Placeholder::make('country')
                                ->content(fn (ServiceOrderAddress $record) => $record->country),
                            Placeholder::make('state')
                                ->content(fn (ServiceOrderAddress $record) => $record->state),
                            Placeholder::make('city')
                                ->label('City/Province')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->city),
                            Placeholder::make('zip_code')
                                ->label('Zip Code')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->zip_code),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                ])
                ->columns(2),
            Section::make(trans('Billing Address'))
                ->relationship('serviceOrderBillingAddress')
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Placeholder::make('address_line_1')
                                ->label('House/Unit/Flr #, Bldg Name, Blk or Lot #')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->address_line_1),
                            Placeholder::make('country')
                                ->content(fn (ServiceOrderAddress $record) => $record->country),
                            Placeholder::make('state')
                                ->content(fn (ServiceOrderAddress $record) => $record->state),
                            Placeholder::make('city')
                                ->label('City/Province')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->city),
                            Placeholder::make('zip_code')
                                ->label('Zip Code')
                                ->translateLabel()
                                ->content(fn (ServiceOrderAddress $record) => $record->zip_code),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                ])
                ->columns(2),
            Group::make()
                ->schema([
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
                            DateTimePicker::make('date')
                                ->minDate(now())
                                ->withoutSeconds()
                                ->default(now())
                                ->disabled()
                                ->hidden()
                                ->timezone(Auth::user()?->timezone),
                            TextInput::make('price')->required()->numeric()->reactive(),
                        ])
                        ->columns(3),
                ])
                ->columns(2),
            Section::make(trans('Service Fill-up Form'))
                ->schema([
                    SchemaFormBuilder::make('customer_form', fn ($record) => $record->service->blueprint->schema)
                        ->schemaData(fn ($record) => $record->service->blueprint->schema),
                ])
                ->hidden(fn (Closure $get) => $get('service_id') === null)
                ->columns(2),
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
                            } catch (ModelNotFoundException $m) {
                                $action->failureNotificationTitle(trans($m->getMessage()))
                                    ->failure();

                                report($m);
                            } catch (Exception $e) {
                                $action->failureNotificationTitle(trans('Something went wrong!'))
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
        return app(GetTaxableInfoAction::class)->computeTotalPriceWithTax(
            ServiceOrderResource::getSubtotal($record->service_price, $additionalCharges),
            $record
        );
    }
}
