<?php

namespace Domain\Support\Payments\Providers;

use Andriichuk\Laracash\Facades\Laracash;
use App\Models\Order\Order;
use App\Services\Payments\Interfaces\HandlesRedirection;
use App\Services\Payments\PaymentDetails;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\Events\PaymentProcessEvent;
use Domain\Support\Payments\DataTransferObjects\PayPalProviderData;
use Domain\Support\Payments\Interfaces\HandlesRedirection as InterfacesHandlesRedirection;
use Domain\Support\Payments\Models\Payment as ModelsPayment;
use Domain\Support\Payments\Providers\Concerns\HandlesRedirection as ConcernsHandlesRedirection;
use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalProvider extends Provider implements InterfacesHandlesRedirection
{
    
    use ConcernsHandlesRedirection;

    protected $name = 'paypal';

    /** @var \PayPal\Rest\ApiContext */
    private $paypalApiContext;

    private PaymentMethod $PaymentMethod;

    public function __construct(PaymentMethod $paymentMethod)
    {
        
        $this->PaymentMethod = $paymentMethod;

        $this->paypalApiContext = new ApiContext(
            new OAuthTokenCredential(
                clientId: $paymentMethod->credentials['paypal_secret_id'],
                clientSecret: $paymentMethod->credentials['paypal_secret_key']
            )
        );
        
        $this->paypalApiContext->setConfig(array(
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path() . '/logs/paypal.log',
            'log.LogLevel' => 'ERROR'
        ));
    }

    public function initRedirection(PayPalProviderData $providerData): void
    {
        $payer = new Payer(['payment_method' => 'paypal']);

        // $itemList = new ItemList;

        // foreach ($providerData->transactionData->item_list as $item) {
        //     $itemList->addItem(new Item([
        //         'name' => $item->name,
        //         'quantity' => $item->quantity,
        //         'currency' => $item->currency,
        //         'price' => $item->price,
        //     ]));
        // }

        $paymentData = $providerData->transactionData->amount;

         $amount = new Amount([
            'currency' => $paymentData->currency,
            'total' => $paymentData->total,
            'details' => new Details(array_filter(get_object_vars($paymentData->details) )),
        ]);

          $transaction = new Transaction([
            'amount' => $amount,
            // 'item_list' => $itemList,
            'description' => $providerData->transactionData->description,
        ]);

        $paymentTransaction = $providerData->model->payments()->create([
            'payment_method_id' => $this->PaymentMethod->id,
            'gateway' => $this->name,
            'amount' => $paymentData->total,
            'status' => 'pending',
        ]);


        $redirectUrls = new RedirectUrls([
            'return_url' => route('tenant.api.payment-callback',[
                'paymentmethod' => $this->PaymentMethod->id,
                'transactionId' => $paymentTransaction->id,
                'status' => 'success',
            ]),
            'cancel_url' => 'http://dimencion.saas-platform.test/api/contents',
        ]);
 

        $payment = new Payment([
            'intent' => 'sale',
            'payer' => $payer,
            'transactions' => [$transaction],
            'redirect_urls' => $redirectUrls,
        ]);

       
        $payment->create($this->paypalApiContext);

        $this->transactionId = $payment->getId();
        $this->redirectUrl = $payment->getApprovalLink();

        dd($this->redirectUrl);
    }

    public function handleRedirectionCallback(Request $request, string $status) 
    {
       
        switch ($status) {
            case 'success':
               
                $paymentTransaction = ModelsPayment::where('id',$request->transactionId)->firstorFail();

                $paymentTransaction->update([
                    'status' => 'paid',
                    'transaction_id' => $request->paymentId
                ]);
                
                // triger and event 
                event(new PaymentProcessEvent($paymentTransaction));

                $payment = Payment::get($request->paymentId, $this->paypalApiContext);
    
                $execution = new PaymentExecution(['payer_id' => $request->PayerID]);
        
                $payment->execute($execution, $this->paypalApiContext);

            default:
                return null;
        }
    }


}
