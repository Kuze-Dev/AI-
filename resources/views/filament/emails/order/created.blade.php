<style>
    .main-container {
        /* text-align: center; */
        padding-top: 2em;
        padding-bottom: 2em;
        padding-left: 16em;
        padding-right: 16em;
        font-size: medium;
    }

    .remarks-container {
        border: 1.5px solid #9ca3af;
        margin-top: 1.5em;
        padding-top: 0.5em;
        padding-bottom: 1.5em;
        padding-left: 1.3em;
        padding-right: 1.3em;
        text-align: left;
        border-radius: 0.8em;
    }

    @media (max-width: 1024px) and (min-width: 769px) {
        .main-container {
            padding-left: 8em;
            padding-right: 8em;
        }
    }

    @media (max-width: 768px) {
        .main-container {
            padding-left: 1.5em;
            padding-right: 1.5em;
        }
    }

    @media (max-width: 576px) {
        .main-container {
            padding-left: 2px;
            padding-right: 2px;
            font-size: small;
        }

        .remarks-container {
            padding: 0.8em;
        }
    }
</style>

<div style="width: 100%; font-family: Arial, Helvetica, sans-serif">
    <div style="width: 100%; max-height: 120px">
        <img src="https://img.freepik.com/free-vector/stylish-glowing-digital-red-lines-banner_1017-23964.jpg"
            alt="" style="width: 100%; height: 120px; object-fit: cover" />
    </div>

    <div class="main-container">
        <h3 style="margin-bottom: 1em; font-weight: bold">
            Your Order has been Placed.
        </h3>

        <p style="line-height: 1.5em">Hi {{ $customer->first_name . $customer->last_name }},</p>

        <p style="line-height: 1.5em">
            Thank you for placing an order with us! Your order number is
            #{{ $order->reference }}, and we received it on
            {{ \Carbon\Carbon::parse($order->created_at)->timezone($timezone)->format('jS F Y \a\t h:i A') }}. You
            have
            chosen {{ $paymentMethod->title }} as your payment method. Our team is currently
            preparing your order, and we'll notify you once it's on the way. We
            hope you have a pleasant shopping experience with us and look forward
            to serving you again soon.
        </p>

        <div class="remarks-container">
            <p style="margin-bottom: 0.5em; font-weight: bold">
                Delivery Details:
            </p>
            <p style="line-height: 1.5em">
                Name: <span> {{ $customer->first_name . $customer->last_name }}</span>
            </p>
            <p style="line-height: 1.5em">Address: <span> {{ $address }}</span></p>
            <p style="line-height: 1.5em">Phone: <span> {{ $customer->mobile }} </span></p>
            <p style="line-height: 1.5em">
                Email: <span> {{ $customer->email }}</span>
            </p>
        </div>

        <div class="remarks-container">
            <p style="margin-bottom: 0.5em; font-weight: bold">Order Details:</p>
            <div
                style="
            display: flex;
            justify-content: flex-start;
            width: 100%;
            align-items: center;
            gap: 2em;
          ">
                <div>
                    <img src="https://i.pinimg.com/originals/67/25/e4/6725e4716ab971ff4b033d47184b27a8.png"
                        alt="" style="width: 60px; height: 60px; object-fit: cover" />
                </div>
                <div>
                    <p>Programmers mug</p>
                    <p>P 99.00</p>
                    <p>Quantity: <span>1</span></p>
                </div>
            </div>
        </div>

        <div class="remarks-container">
            <p style="margin-bottom: 0.5em; font-weight: bold">Order Summary:</p>
            <div
                style="
            display: flex;
            justify-content: flex-start;
            width: 100%;
            align-items: center;
            gap: 2em;
            border-bottom: 1.3px solid #9ca3af;
          ">
                <div style="width: 100%">
                    <p>Subtotal:</p>
                    <p>Tax fee:</p>
                    <p>Shipping fee:</p>
                    <p>Total Saving:</p>
                    <p>Grand Total:</p>
                </div>
                <div style="width: 100%; text-align: right">
                    <p>P 99.00</p>
                    <p>P 99.00</p>
                    <p>P 99.00</p>
                    <p>P 99.00</p>
                    <p>P 99.00</p>
                </div>
            </div>
            <div
                style="
            display: flex;
            justify-content: flex-start;
            width: 100%;
            align-items: center;
            gap: 2em;
          ">
                <div style="width: 100%">
                    <p>Shipping Option:</p>
                    <p>Paid by:</p>
                </div>
                <div style="width: 100%; text-align: right">
                    <p>USPS</p>
                    <p>Cash on Delivery</p>
                </div>
            </div>
        </div>
    </div>
</div>
