<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use App\Settings\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class GenerateServiceTransactionReceiptAction
{
    /** @throws Throwable */
    public function execute(ServiceTransaction $serviceTransaction): Media
    {
        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceTransaction->serviceOrder;

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = $serviceOrder->customer;

        /** @var string $filename */
        $filename = Str::snake(app(SiteSettings::class)->name).'_'.
            now()->format('m_Y_His');

        /** @var string $path */
        $path = $serviceTransaction->getKey().'-'.
            $serviceOrder->getKey().
            $customer->getKey().DIRECTORY_SEPARATOR.
            $filename.'.pdf';

        $disk = config('filament.default_filesystem_disk');

        if (is_null($serviceTransaction->service_bill_id)) {
            Pdf::loadView(
                'web.layouts.service-order.receipts.partial',
                ['transaction' => $serviceTransaction]
            )->save($path, $disk);
        } else {
            Pdf::loadView(
                'web.layouts.service-order.receipts.default',
                ['transaction' => $serviceTransaction]
            )->save($path, $disk);
        }

        $customer->addMediaFromDisk($path, $disk)
            ->toMediaCollection('receipts');

        return $customer->getMedia('receipts')
            ->where('name', $filename)
            ->firstOrFail();
    }
}
