<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\FilamentTenant\Resources\OrderResource;
use App\FilamentTenant\Support;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\ProductVariant;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ViewOrderDetails extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeading(): string|Htmlable
    {
        return trans('Order Details #').$this->record->reference;
    }

    protected function getRelationManagers(): array
    {
        return [];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Group::make()
                        ->schema($this->getSections())
                        ->columnSpan(2),
                    OrderResource::summaryCard(),
                ])->columns(3),
        ];
    }

    private function getSections(): array
    {
        $sections = [];

        foreach ($this->record->orderLines as $index => $orderLine) {
            $sectionIndex = $index + 1;
            $sections[] = Forms\Components\Section::make(strval($sectionIndex))
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Support\Carousel::make('order_line_carousel')
                                ->value(function () use ($orderLine) {
                                    return $orderLine?->getMedia('order_line_images')->toArray() ?? [];
                                }),
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\FileUpload::make('image_'.$sectionIndex)
                                        ->formatStateUsing(function () use ($orderLine) {

                                            return $orderLine?->getMedia('order_line_images')
                                                ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                                ->toArray() ?? [];
                                        })
                                        ->disableLabel()
                                        ->hidden(function () use ($orderLine) {
                                            return (bool) (empty($orderLine->getFirstMediaUrl('order_line_images')));
                                        })
                                        ->image()
                                        ->imagePreviewHeight('120')
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
                                                    // This driver does not support creating temporary URLs.
                                                }
                                            }

                                            return $media?->getUrl();
                                        })
                                        ->columnSpan(1),
                                ])
                                ->columnSpan(1),
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Placeholder::make('product_'.$sectionIndex)
                                                        ->label(trans('Product'))
                                                        ->content($orderLine->name),
                                                    Forms\Components\Placeholder::make('variant_'.$sectionIndex)
                                                        ->label(trans('Variant'))
                                                        ->content(function () use ($orderLine) {
                                                            if ($orderLine->purchasable_type == ProductVariant::class) {
                                                                $combinations = array_values($orderLine->purchasable_data['combination']);
                                                                $optionValues = array_column($combinations, 'option_value');
                                                                $variantString = implode(' / ', array_map('ucfirst', $optionValues));

                                                                return $variantString;
                                                            }

                                                            return 'N/A';
                                                        }),
                                                ]),
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Placeholder::make('quantity_'.$sectionIndex)
                                                        ->label(trans('Quantity'))
                                                        ->content($orderLine->quantity),
                                                    Forms\Components\Placeholder::make('amount_'.$sectionIndex)
                                                        ->label(trans('Amount'))
                                                        ->content(function () use ($orderLine) {
                                                            return $orderLine->order->currency_symbol.' '.number_format($orderLine->sub_total, 2, '.', '');
                                                        }),
                                                ]),
                                        ]),
                                ])
                                ->columnSpan(2),
                        ])->columns(3),
                    Support\Divider::make(''),
                    self::viewRemarksButton($orderLine, $sectionIndex),
                ])
                ->collapsible();
        }

        return $sections;
    }

    private function viewRemarksButton(OrderLine $orderLine, int $sectionIndex): Support\ButtonAction
    {
        return Support\ButtonAction::make('view_remarks_'.$sectionIndex)
            ->disableLabel()
            ->execute(function () use ($sectionIndex, $orderLine) {
                return Forms\Components\Actions\Action::make('view_remarks_btn_'.$sectionIndex)
                    ->color('secondary')
                    ->label(trans('View Remarks'))
                    ->size('sm')
                    ->action(function () {
                    })
                    ->modalActions([])
                    ->modalHeading(trans('Customer Remarks'))
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Placeholder::make('remarks_'.$sectionIndex)
                            ->label(trans('Remarks'))
                            ->hidden(is_null($orderLine->remarks_data) ? true : false)
                            ->content($orderLine->remarks_data['notes'] ?? ''),
                        Forms\Components\FileUpload::make('customer_upload_'.$sectionIndex)
                            ->label(trans('Customer Upload'))
                            ->formatStateUsing(function () use ($orderLine) {
                                return $orderLine->getMedia('order_line_notes')
                                    ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                    ->toArray();
                            })
                            ->hidden(function () use ($orderLine) {
                                return (bool) (empty($orderLine->getFirstMediaUrl('order_line_notes')));
                            })
                            ->disabled()
                            ->multiple()
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
                            }),
                    ])
                    ->slideOver()
                    ->icon('heroicon-o-document');
            })
            ->hidden(is_null($orderLine->remarks_data) &&
                empty($orderLine->getFirstMediaUrl('order_line_notes')) ? true : false)
            ->fullWidth()
            ->size('md');
    }
}
