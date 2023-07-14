<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\FilamentTenant\Resources\OrderResource;
use Domain\Product\Models\ProductVariant;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;
use App\FilamentTenant\Support;

class ViewOrderDetails extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeading(): string|Htmlable
    {
        return 'Order Details #' . $this->record->reference;
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
                        ->schema($this->getSections())->columnSpan(2),
                    OrderResource::summaryCard(),
                ])->columns(3),
        ];
    }

    private function getSections()
    {
        $sections = [];

        foreach ($this->record->orderLines as $index => $orderLine) {
            $sectionIndex = $index + 1;
            $sections[] = Forms\Components\Section::make(strval($sectionIndex))
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\FileUpload::make('image_' . $sectionIndex)
                                        ->formatStateUsing(function ($record) use ($orderLine) {
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
                                        })->columnSpan(1),
                                ])->columnSpan(1),
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Placeholder::make('product_'  . $sectionIndex)->label('Product')
                                                        ->content($orderLine->name),
                                                    Forms\Components\Placeholder::make('variant_'  . $sectionIndex)->label('Variant')
                                                        ->content(function () use ($orderLine) {
                                                            if ($orderLine->purchasable_type == ProductVariant::class) {
                                                                $variant = array_values($orderLine->purchasable_data['combination']);
                                                                $variantString = implode(' / ', array_map('ucfirst', $variant));

                                                                return $variantString;
                                                            }

                                                            return 'N/A';
                                                        }),
                                                ]),
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Placeholder::make('quantity_' . $sectionIndex)->label('Quantity')
                                                        ->content($orderLine->quantity),
                                                    Forms\Components\Placeholder::make('amount_' . $sectionIndex)->label('Amount')
                                                        ->content($orderLine->sub_total),
                                                ]),
                                        ]),
                                ])->columnSpan(2),
                        ])->columns(3),
                    Support\Divider::make(''),
                    Support\ButtonAction::make('view_remarks_' . $sectionIndex)
                        ->disableLabel()
                        ->execute(function () use ($sectionIndex, $orderLine) {
                            return Forms\Components\Actions\Action::make('view_remarks_btn_' . $sectionIndex)
                                ->color('secondary')
                                ->label('View Remarks')
                                ->size('sm')
                                ->action(function () {
                                })
                                ->modalActions([])
                                ->modalHeading('Customer Remarks')
                                ->modalWidth('lg')
                                ->form([
                                    Forms\Components\Placeholder::make('remarks_' . $sectionIndex)->label('Remarks')
                                        ->hidden(is_null($orderLine->remarks_data) ? true : false)
                                        ->content($orderLine->remarks_data['notes'] ?? ''),
                                    Forms\Components\FileUpload::make('customer_upload_' . $sectionIndex)->label('Customer Upload')
                                        ->formatStateUsing(function ($record) use ($orderLine) {
                                            return $orderLine?->getMedia('order_line_notes')
                                                ->mapWithKeys(fn (Media $file) => [$file->uuid => $file->uuid])
                                                ->toArray() ?? [];
                                        })
                                        ->hidden(function () use ($orderLine) {
                                            return (bool) (empty($orderLine->getFirstMediaUrl('order_line_notes')));
                                        })
                                        ->multiple()
                                        ->image()
                                        ->getUploadedFileUrlUsing(static function (Forms\Components\FileUpload $component, string $file): ?string {
                                            $mediaClass = config('media-library.media_model', Media::class);

                                            /** @var ?Media $media */
                                            $media = $mediaClass::findByUuid($file);

                                            if ($component->getVisibility() === 'private') {
                                                try {
                                                    return $media?->getTemporaryUrl(now()->addMinutes(5));
                                                } catch (Throwable $exception) {
                                                }
                                            }

                                            return $media?->getUrl();
                                        })->disabled(),
                                ])
                                ->slideOver()
                                ->icon('heroicon-o-document');
                        })
                        ->hidden(is_null($orderLine->remarks_data) && empty($orderLine->getFirstMediaUrl('order_line_notes')) ? true : false)
                        ->fullWidth()->size("md"),
                ])->collapsible();
        }

        return $sections;
    }
}
