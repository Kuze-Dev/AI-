<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Payment;

use App\Settings\ECommerceSettings;
use App\Settings\SiteSettings;
use Domain\Page\Models\Page;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\TransactionData;
use Throwable;

class PaymentCallbackController
{
    #[Get('/paymentcallback/{paymentmethod}/{transactionId}/{status}', name: 'payment-callback')]
    public function __invoke(
        string $paymentmethod,
        string $transactionId,
        string $status,
        Request $request
    ): RedirectResponse {

        try {

            $paymentMethod = PaymentMethod::findorfail($paymentmethod);

            $paymentModel = Payment::with('payable')->where('id', $transactionId)->first();

            /** @var \Domain\Payments\Interfaces\PayableInterface */
            $payableModel = $paymentModel?->payable;

            $data = array_merge($request->all(), ['status' => $status]);

            app(PaymentManagerInterface::class)
                ->driver($paymentMethod->gateway)
                ->capture($paymentModel, $data);

            $baseUrl = app(ECommerceSettings::class)->domainWithScheme()
                    ?? app(SiteSettings::class)->domainWithScheme();

            return redirect()->away(
                $baseUrl.'/checkout'.'/'.$status.'?reference='.$payableModel->getReferenceNumber()
            );

        } catch (Throwable $th) {
            throw $th;
        }

    }

    // #[Get('/test-payment', name: 'payment-test')]
    // public function test()
    // {
    //     // $paymentMethod = PaymentMethod::where('slug', 'paypal')->first();

    //     $page = Page::first();

    //     $providerData = new CreatepaymentData(
    //         transactionData: TransactionData::fromArray(
    //             [
    //                 'reference_id' => '123',
    //                 'amount' => AmountData::fromArray([
    //                     'currency' => 'PHP',
    //                     'total' => '1000.00',
    //                     'details' => PaymentDetailsData::fromArray(
    //                         [
    //                             'subtotal' => '950.00',
    //                             'shipping' => '50.00',
    //                         ]
    //                     ),
    //                 ]),
    //                 'item_list' => [
    //                     [
    //                         'sku' => 'SKU-4958',
    //                         'name' => 'Product One',
    //                         'description' => 'Sample Product',
    //                         'quantity' => '1',
    //                         'price' => '950',
    //                         'currency' => 'PHP',
    //                         'tax' => '0',
    //                         'category' => 'Product',
    //                     ],
    //                 ],
    //                 'description' => 'payment request',
    //             ],
    //         ),
    //         payment_driver: 'paypal'
    //     );

    //     dump(
    //         app(CreatePaymentAction::class)
    //             ->execute($page, $providerData)
    //     );

    // }
}
