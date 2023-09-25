<style>
    .main-container {
        padding-top: 2em;
        padding-bottom: 2em;
        padding-left: 16em;
        padding-right: 16em;
        font-size: medium;
        font-family: 'Arial', 'Helvetica', 'Impact', 'Haettenschweiler', 'Arial Narrow Bold', sans-serif;
    }

    .header {
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1.3px solid #e5e7eb;
        padding-bottom: 1em;
        font-family: 'Arial', 'Helvetica', 'Impact', 'Haettenschweiler', 'Arial Narrow Bold', sans-serif;
    }

    .header img {
        width: 100px;
        height: 100px;
        object-fit: cover
    }

    .header .col-2 {
        padding-left: 1em;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;

    }

    .title {
        font-size: xx-large;
        font-weight: bold;
    }

    .subtitle {
        color: #3f3f3f;
        padding-left: 4px;
    }

    .card {
        border: 1.5px solid #E5E7EB;
        margin-top: 1.5em;
        padding-top: 0.5em;
        padding-bottom: 1.5em;
        padding-left: 1.3em;
        padding-right: 1.3em;
        text-align: left;
        border-radius: 0.8em;
    }

    .card-title {
        border-bottom: 1.3px solid #E5E7EB;
        margin-bottom: 0.3em;
    }

    .card-title p {
        font-weight: bold
    }

    .summary-list {
        display: flex;
        justify-content: space-between
    }

    .horizontal-line {
        border-bottom: 1.3px solid #E5E7EB
    }

    .order-details {
        display: flex;
        justify-content: flex-start;
        border-bottom: 1.3px solid #E5E7EB;
        padding-bottom: 0.5em;
        align-items: flex-start;
    }

    .order-details img {
        width: 120px;
        height: 120px;
        object-fit: cover
    }

    .footer {
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-top: 1px solid #e5e7eb;
        padding-top: 15px;
    }

    .footer p {
        margin: 0;
        padding: 0;
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

        .summary-list .col-1 {
            width: 70%;
        }

        .summary-list .col-2 {
            width: 30%;
        }
    }

    @media (max-width: 576px) {
        .main-container {
            padding-left: 2px;
            padding-right: 2px;
            font-size: small;
        }

        .summary-list {
            padding: 0.8em;
        }

        .summary-list .col-1 {
            width: 50%;
        }

        .summary-list .col-2 {
            width: 50%;
        }

        .order-details img {
            width: 80px;
            height: 80px
        }

        .title {
            font-size: medium;
        }

        .subtitle {
            font-size: small;
        }

        .header img {
            width: 70px;
            height: 70px;
            object-fit: cover
        }
    }
</style>

<div style="width: 100%;">

    <div class="header">
        @if ($logo)
            <div>
                <img src={{ $logo }} alt="" />
            </div>
        @endif
        <div class="col-2">
            <div>
                <span class="title">{{ $title }}</span>
            </div>
            <div><span class="subtitle">{{ $description }}</span></div>
        </div>
    </div>

    <div class="main-container">
        <h3 style="margin-bottom: 1em; font-weight: bold">
            Your Order has been Placed.
        </h3>

        <p style="line-height: 1.5em">Hi {{ $customer->first_name . $customer->last_name }},</p>

        <p style="line-height: 1.5em">
            Thank you for placing an order with us! Your order number is
            #{{ $order->reference }}, and we received it on
            {{ \Carbon\Carbon::parse($order->created_at)->timezone('UTC')->format('F d, Y g:i A') }} (UTC). You
            have
            chosen {{ $paymentMethod->title }} as your payment method. Our team is currently
            preparing your order, and we'll notify you once it's on the way. We
            hope you have a pleasant shopping experience with us and look forward
            to serving you again soon.
        </p>

        <div class="card">
            <div class="card-title">
                <p class=>Delivery Details:</p>
            </div>

            <p style="line-height: 1.5em">
                Name: <span> {{ $customer->first_name . $customer->last_name }}</span>
            </p>
            <p style="line-height: 1.5em">Address: <span> {{ $address }}</span></p>
            <p style="line-height: 1.5em">Phone: <span> {{ $customer->mobile }} </span></p>
            <p style="line-height: 1.5em">
                Email: <span> {{ $customer->email }}</span>
            </p>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom: 1em">
                <p class=>Order Details:</p>
            </div>

            @foreach ($order->orderLines as $orderLine)
                <div style="margin-bottom:1.5em">
                    <div class="order-details">
                        <div>
                            <img src="{{ $orderLine->getFirstMediaUrl('order_line_images') }}" alt="" />
                        </div>
                        <div
                            style="
                            display: flex;
                            align-items: flex-start;
                            justify-content: flex-end;
                            flex-direction: column;
                            gap: 0.7em;
                            padding-left: 1em;
                            font-size: 1em;
                        ">
                            <div>{{ $orderLine->name }}</div>
                            <div>
                                {{ $order->currency_symbol }}
                                {{ number_format((float) $orderLine->sub_total, 2, '.', ',') }}
                            </div>
                            @if (isset($orderLine->purchasable_data['combination']))
                                @php
                                    $combinations = array_values($orderLine->purchasable_data['combination']);
                                    $optionValues = array_column($combinations, 'option_value');
                                    $variantString = implode(' / ', array_map('ucfirst', $optionValues));
                                @endphp

                                <div style="height: 20px;">
                                    <div><span style="color:#3f3f3f;">{{ $variantString }}</span> </div>
                                </div>
                            @endif

                            <div style="height: 20px;">
                                <span style="color:#3f3f3f">Quantity:</span>
                                <span>{{ $orderLine->quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title">
                <p class=>Order Summary:</p>
            </div>
            <div class="summary-list horizontal-line">
                <div>
                    <p>Shipping Method:</p>
                    <p>Payment Method:</p>
                </div>
                <div style="text-align: right">
                    <p>{{ $shippingMethod->title }}</p>
                    <p>{{ $paymentMethod->title }}</p>
                </div>
            </div>

            <div class="summary-list horizontal-line">
                <div>
                    <p>Subtotal:</p>
                    <p>Tax fee:</p>
                    <p>Shipping fee:</p>
                    <p>Total Saving:</p>
                </div>
                <div style="text-align: right">
                    <p>{{ $order->currency_symbol }}
                        {{ number_format((float) $order->sub_total, 2, '.', ',') }}
                    </p>
                    <p>{{ $order->currency_symbol }}
                        {{ number_format((float) $order->tax_total, 2, '.', ',') }}
                    </p>
                    <p>{{ $order->currency_symbol }}
                        {{ number_format((float) $order->shipping_total, 2, '.', ',') }}
                    </p>
                    <p>{{ $order->currency_symbol }}
                        {{ number_format((float) $order->discount_total, 2, '.', ',') }}
                    </p>
                </div>
            </div>
            <div class="summary-list ">
                <div>
                    <p>Grand Total:</p>
                </div>
                <div style="text-align: right">
                    <p>{{ $order->currency_symbol }}
                        {{ number_format((float) $order->total, 2, '.', ',') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        @php
            $htmlFooter = $footer;
        @endphp
        {!! $htmlFooter !!}
    </div>
</div>
