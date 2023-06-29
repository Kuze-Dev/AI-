<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Payment;

use Domain\Page\Models\Page;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\Contracts\PaymentManagerInterface;
use Domain\Support\Payments\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Domain\Support\Payments\Actions\CreatePaymentAction;
use Domain\Support\Payments\DataTransferObjects\AmountData;
use Domain\Support\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Support\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Support\Payments\DataTransferObjects\TransactionData;
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

            $paymentModel = Payment::find($transactionId);

            $data = array_merge($request->all(), ['status' => $status]);

            // dump(
            app(PaymentManagerInterface::class)
                ->driver($paymentMethod->gateway)
                ->capture($paymentModel, $data);
            // );

            #redirect to FE order summary or order page.
            return redirect()->away('/');
        } catch (Throwable $th) {
            throw $th;
        }

    }

    // #[Get('/test-payment', name: 'payment-test')]
    // public function test()
    // {
    //     $paymentMethod = PaymentMethod::where('slug', 'cod')->first();

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
    //             ]
    //             ),
    //             payment_driver: 'paypal'

    //     );

    //     dump(
    //         app(CreatePaymentAction::class)
    //             ->execute($page, $providerData)
    //     );

    // }
}
