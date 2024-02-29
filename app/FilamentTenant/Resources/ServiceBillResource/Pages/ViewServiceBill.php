<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceBillResource\Pages;

use App\FilamentTenant\Resources\ServiceBillResource;
use App\FilamentTenant\Resources\ServiceOrderResource;
use App\FilamentTenant\Support;
use App\FilamentTenant\Support\BadgeLabel;
use App\FilamentTenant\Support\ButtonAction;
use App\FilamentTenant\Support\Divider;
use App\FilamentTenant\Support\TextLabel;
use Closure;
use Domain\Admin\Models\Admin;
use Domain\Payments\Enums\PaymentRemark;
use Domain\Payments\Enums\PaymentStatus;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Events\AdminServiceBillBankPaymentEvent;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\Taxation\Enums\PriceDisplay;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ViewServiceBill extends ViewRecord
{
    protected static string $resource = ServiceBillResource::class;

    public mixed $ownerRecord;

    public function getHeading(): string|Htmlable
    {
        return trans('Service Bill Details #').$this->record->reference;
    }

    //    public function getBreadcrumbs(): array
    //    {
    //        $resource = static::getResource();
    //
    //        $breadcrumb = $this->getBreadcrumb();
    //
    //        return array_merge(
    //            [
    //                ServiceOrderResource::getUrl('index') => ServiceOrderResource::getBreadcrumb(),
    //                ServiceOrderResource::getUrl('view', [$this->ownerRecord]) => $this->ownerRecord,
    //                $resource::getUrl('view', [$this->ownerRecord, $this->record->reference]) => $this->record->reference,
    //            ],
    //            (filled($breadcrumb) ? [$breadcrumb] : []),
    //        );
    //    }

    protected function getFormSchema(): array
    {
        $admin = Admin::first();

        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()->columnSpan(2)->schema([
                        Section::make(trans('Service'))->schema([
                            Grid::make(2)->schema([
                                Placeholder::make('service')
                                    ->content(fn ($record) => $record->serviceOrder->service_name),
                                Placeholder::make('service Price')
                                    ->content(fn ($record) => $record->serviceOrder->currency_symbol.' '.number_format($record->serviceOrder->service_price, 2, '.', ',')),
                            ]),
                        ]),
                        Section::make(trans('Additional Charges'))
                            ->schema([
                                Forms\Components\Group::make()->schema($this->getAdditionalCharges()),
                            ])->visible(fn ($record) => ! empty($record->additional_charges)),

                    ]),

                    Section::make(trans('Summary'))->columnSpan(1)->schema([
                        Forms\Components\Group::make()->columns(2)
                            ->schema([
                                TextLabel::make('')
                                    ->label(trans('Status'))
                                    ->alignLeft()
                                    ->size('md')
                                    ->inline()
                                    ->readOnly(),
                                BadgeLabel::make(trans('status'))->formatStateUsing(function (string $state): string {
                                    $newState = str_replace('_', ' ', strtolower($state));

                                    return ucfirst($newState);
                                })
                                    ->color(function ($state) {
                                        $newState = str_replace(' ', '_', strtolower($state));

                                        return match ($newState) {
                                            ServiceBillStatus::PENDING->value => 'warning',
                                            ServiceBillStatus::PAID->value => 'success',
                                            default => 'secondary',
                                        };
                                    })->inline()
                                    ->alignRight(),
                            ]),
                        self::summaryProofOfPaymentButton(),
                        Divider::make(''),
                        Forms\Components\Group::make()->columns(2)->schema([
                            TextLabel::make('')
                                ->label(trans('Service Price'))
                                ->alignLeft()
                                ->size('md')
                                ->inline()
                                ->readOnly(),
                            TextLabel::make('')
                                ->label(fn ($record) => $record->serviceOrder->currency_symbol.' '.number_format($record->service_price, 2, '.', ','))
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
                                ->label(fn ($record, \Filament\Forms\Get $get) => $record->serviceOrder->currency_symbol.' '.number_format(array_reduce($get('additional_charges'), function ($carry, $data) {
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
                                    ->label(fn (ServiceBill $record, \Filament\Forms\Get $get) => $record->tax_display == PriceDisplay::INCLUSIVE->value ? 'Inclusive'
                                        :
                                        $record->serviceOrder?->currency_symbol.' '.number_format($record->tax_total, 2, '.', '.'))
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
                                ->label(fn (ServiceBill $record, \Filament\Forms\Get $get) => $record->serviceOrder?->currency_symbol.' '.number_format($record->total_amount, 2, '.', '.'))
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

    private function getAdditionalCharges(): array
    {
        $schema = [];
        $schema[] = Forms\Components\Group::make()->columns(3)->schema([
            Support\TextLabel::make('')
                ->label(trans('Name'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),
            Support\TextLabel::make('')
                ->label(trans('Quantity'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),
            Support\TextLabel::make('')
                ->label(trans('Price'))
                ->alignLeft()
                ->size('sm')
                ->inline()
                ->readOnly(),

            Support\Divider::make('')->columnSpan(3),
        ]);
        foreach ($this->record->additional_charges as $additionalcharge) {
            $schema[] = Forms\Components\Group::make()->columns(3)->schema([
                Support\TextLabel::make('')
                    ->label($additionalcharge['name'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label(''.$additionalcharge['quantity'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\TextLabel::make('')
                    ->label($this->record->serviceOrder->currency_symbol.$additionalcharge['price'])
                    ->alignLeft()
                    ->size('md')
                    ->inline()
                    ->readOnly(),
                Support\Divider::make('')->columnSpan(3),
            ]);
        }

        return $schema;
    }

    private static function summaryProofOfPaymentButton(): ButtonAction
    {
        return ButtonAction::make('proof_of_payment')
            ->disableLabel()
            ->execute(function (ServiceBill $record, \Filament\Forms\Set $set) {
                $footerActions = self::showProofOfPaymentActions($record, $set);

                return $footerActions;
            })
            ->fullWidth()
            ->size('md')
            ->hidden(function (ServiceBill $record) {

                if ($record->status === ServiceBillStatus::FOR_APPROVAL) {
                    return false;
                }

                return true;
            });
    }

    private static function showProofOfPaymentActions(ServiceBill $record, Closure $set): Action
    {
        $order = $record;

        return Action::make('proof_of_payment')
            ->color('gray')
            ->label(trans('View Proof of payment'))
            ->size('sm')
            ->action(function (array $data) use ($record, $set) {
                DB::transaction(function () use ($data, $record, $set) {

                    $paymentRemarks = $data['payment_remarks'];
                    $message = $data['message'];

                    /** @var \Domain\Payments\Models\Payment $payment */
                    $payment = $record->latestPayment();

                    if (is_null($paymentRemarks)) {
                        Notification::make()
                            ->title(trans('No status found!'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $result = $payment->update([
                        'remarks' => $paymentRemarks,
                        'admin_message' => $message,
                    ]);

                    if (! $result) {
                        Notification::make()
                            ->title(trans('Invalid Data!'))
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($paymentRemarks === PaymentRemark::DECLINED->value) {
                        $payment->update([
                            'status' => 'pending',
                        ]);

                        $record->update([
                            'status' => ServiceBillStatus::PENDING,
                        ]);

                        $set('status', ucfirst(ServiceBillStatus::PENDING->value));
                    }

                    if ($paymentRemarks === PaymentRemark::APPROVED->value) {
                        $payment->update([
                            'status' => PaymentStatus::PAID,
                        ]);

                        $record->update([
                            'status' => ServiceBillStatus::PAID,
                        ]);

                        $set('status', ucfirst(ServiceBillStatus::PAID->value));

                        Notification::make()
                            ->title(trans('Proof of payment updated successfully'))
                            ->success()
                            ->send();
                        try {
                            event(new AdminServiceBillBankPaymentEvent(
                                $record,
                                $paymentRemarks,
                            ));
                        } catch (ModelNotFoundException $e) {
                            Notification::make()
                                ->title(trans($e->getMessage()))
                                ->danger()
                                ->send();
                        }
                    }

                });
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
