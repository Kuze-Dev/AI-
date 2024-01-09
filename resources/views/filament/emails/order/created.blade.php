@php use Illuminate\Support\Carbon; @endphp
<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div
                    style="font-family: Arial, sans-serif; line-height: 1.6;border: thin solid #ddd; border-radius: 12px; padding: 40px 20px">
                    <center>
                        <div style="max-width: 600px;  padding: 20px; border-bottom: 1.3px solid #e5e7eb;">
                            @if ($logo)
                                <div style="max-width: 300px;">
                                    <img style="max-width: 100%; height: auto;"
                                         src="{{$logo}}"
                                         alt=""/>
                                </div>
                            @endif
                            <div style="max-width: 600px; padding: 20px;">
                                <div>
                                    <span style="font-size: xx-large; font-weight: bold;">{{ $title }}</span>
                                </div>
                                <div>
                                    <span style="color: #3f3f3f; padding-left: 4px;">{{ $description }}</span>
                                </div>
                            </div>
                        </div>
                    </center>
                    <div style="max-width: 600px; padding: 20px;">
                        <div align="center">
                            <h3 style="padding-bottom: 3em; font-weight: bold">
                                Your Order has been Placed.
                            </h3>
                        </div>

                        <p style="line-height: 1.5em" align="left">
                            Hi
                            <strong>
                                {{ $customer->first_name . ' ' . $customer->last_name }}
                            </strong>
                            ,
                        </p>

                        <p style="line-height: 1.5em" align="left">
                            Thank you for placing an order with us! Your order number is
                            <strong>#{{ $order->reference }} </strong>, and we received it on
                            <strong>{{ Carbon::parse($order->created_at)->timezone('UTC')->format('F d, Y g:i A') }}
                                (UTC)</strong>. You
                            have
                            chosen <strong>{{ $paymentMethod->title }}</strong> as your payment method. Our team is
                            currently
                            preparing your order, and we'll notify you once it's on the way. We
                            hope you have a pleasant shopping experience with us and look forward
                            to serving you again soon.
                        </p>
                            <div style="padding-bottom: 30px">
                            <table style="width: 100%; border-collapse: collapse; ">
                                <tbody>
                                <tr style="border: thin solid #ddd;">
                                    <td style="padding: 10px; font-weight: bold">
                                        Delivery Details
                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">
                                        Name:
                                    </td>
                                    <td>{{ $customer->first_name . ' ' . $customer->last_name }}</td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">
                                        Address:
                                    </td>
                                    <td>{{ $address }}</td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">
                                        Phone:
                                    </td>
                                    <td>{{ $customer->mobile }}</td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">
                                        Email:
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                </tr>
                                </tbody>
                            </table>
                            </div>

                        <table style="width: 100%; border-collapse: collapse; padding-top: 20px; padding-bottom: 40px;">
                            <tbody>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px; font-weight: bold">
                                    Order Details
                                </td>
                                <td></td>
                            </tr>
                            @foreach ($order->orderLines as $orderLine)
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">
                                        <div>
                                            <center>
                                                <img style="max-width: 100px" height="auto" width="100%" align="center"
                                                     src="{{ $orderLine->getFirstMediaUrl('order_line_images') }}"
                                                     alt=""/>
                                            </center>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="padding: 10px">{{ $orderLine->name }}</div>
                                        <div style="padding: 10px">
                                            {{ $order->currency_symbol }}
                                            {{ number_format((float) $orderLine->sub_total, 2, '.', ',') }}
                                        </div>
                                        @if (isset($orderLine->purchasable_data['combination']))
                                            @php
                                                $combinations = array_values($orderLine->purchasable_data['combination']);
                                                $optionValues = array_column($combinations, 'option_value');
                                                $variantString = implode(' / ', array_map('ucfirst', $optionValues));
                                            @endphp
                                            <div style="padding: 10px">
                                                <div><span style="color:#3f3f3f;">{{ $variantString }}</span></div>
                                            </div>
                                        @endif
                                        <div style="padding: 10px">
                                            <span style="color:#3f3f3f">Quantity:</span>
                                            <span>{{ $orderLine->quantity }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <table style="width: 100%; border-collapse: collapse; padding-top: 20px; padding-bottom: 40px;">
                            <tbody>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px; font-weight: bold">
                                    Order Summary
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                <td style="padding: 10px;">Shipping Method:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $shippingMethod->title }}</td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px;">Payment Method:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $paymentMethod->title }}</td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                <td style="padding: 10px;">Subtotal:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $order->currency_symbol }}
                                    {{ number_format((float) $order->sub_total, 2, '.', ',') }}</td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                <td style="padding: 10px;">Tax fee:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $order->currency_symbol }}
                                    {{ number_format((float) $order->tax_total, 2, '.', ',') }}</td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                <td style="padding: 10px;">Shipping fee:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $order->currency_symbol }}
                                    {{ number_format((float) $order->shipping_total, 2, '.', ',') }}</td>
                            </tr>
                            <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                <td style="padding: 10px;">Total Savings:</td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">{{ $order->currency_symbol }}
                                    {{ number_format((float) $order->discount_total, 2, '.', ',') }}</td>
                            </tr>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px;"><strong>Grand Total:</strong></td>
                                <td style="padding: 10px;"></td>
                                <td style="padding: 10px;">
                                    <strong>
                                        {{ $order->currency_symbol }}
                                        {{ number_format((float) $order->total, 2, '.', ',') }}
                                    </strong>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px">
                        <center>
                            @php
                                $htmlFooter = $footer;
                            @endphp
                            {!! $htmlFooter !!}
                        </center>
                    </div>
                </div>
    </table>
    </tr>
    </td>
</center>
