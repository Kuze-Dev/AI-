<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div style="font-family: Arial, sans-serif; line-height: 1.6;border: thin solid #ddd; border-radius: 12px; padding: 40px 20px">
                    <center>
                    <div style="max-width: 600px; padding: 20px; border-bottom: 1.3px solid #e5e7eb;">
                        @if ($logo)
                            <div style="max-width: 300px;">
                                <img style="max-width: 100%; height: auto;"
                                     src="{{$logo}}"
                                     alt=""/>
                            </div>
                        @endif
                        <div style="max-width: 600px; padding: 20px;" align="center">
                            <div>
                                <span style="font-size: xx-large; font-weight: bold;">{{ $title }}</span>
                            </div>
                            <div><span style="color: #3f3f3f; padding-left: 4px;">{{ $description }}</span></div>
                        </div>
                    </div>
                    </center>
                    <div style="max-width: 600px; padding: 20px;">
                        <div align="center">
                            <h3 style="padding-bottom: 3em; font-weight: bold; color: #333;">
                                New Service Bill <strong>#{{$serviceBill->reference}}</strong>
                            </h3>
                        </div>

                        <p style="line-height: 1.5em">Hi <strong>{{ $serviceBill->serviceOrder->customer_full_name }}</strong>,</p>
                        <p style="color: #555;"> Thank you for being a customer. A summary of your bill is below.</p>

                        <table style="width: 100%; border-collapse: collapse; padding-top: 20px; padding-bottom: 40px;">
                            <tbody>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px; font-weight: bold">
                                    Service Order Summary
                                </td>
                                <td>Quantity</td>
                                <td>Price</td>
                            </tr>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px;">{{ $serviceBill->serviceOrder->service_name }}</td>
                                <td> 1x</td>
                                <td style="padding: 10px;">{{ $serviceBill->serviceOrder->currency_symbol }}
                                    {{ number_format((float) $serviceBill->serviceOrder->service_price, 2, '.', ',') }}</td>
                            </tr>
                            @if (!empty($serviceBill->additional_charges))
                                <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                    <td style="padding: 10px; font-weight: bold">
                                        Additional Charges
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @foreach ($serviceBill->additional_charges as $additionalCharges)
                                    <tr style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;">
                                        <td style="padding: 10px;">{{ $additionalCharges['name'] }}</td>
                                        <td style="padding: 10px;">{{ $additionalCharges['quantity'] }}x</td>
                                        <td style="padding: 10px;">{{ $serviceBill->serviceOrder->currency_symbol }}
                                            {{number_format((float)
                                            $additionalCharges['price'], 2,
                                            '.', ',') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px;">Grand Total:</td>
                                <td style=""></td>
                                <td style="padding: 10px;">{{ $serviceBill->serviceOrder->currency_symbol }}
                                    {{ number_format((float) $serviceBill->total_amount, 2, '.', ',') }}</td>
                            </tr>
                            </tbody>
                        </table>

                        <div style="; padding-bottom: 30px;" align="center">
                            <a href="{{$url}}" style="
               padding: 10px 20px;
               background-color:
               #40414b; color: #fff;
               font-weight: bold;
               cursor: pointer;
               border: none;">
                                Proceed to payment
                            </a>
                        </div>
                    </div>
                    <div
                        style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px;" align="center">
                        @php
                            $htmlFooter = $footer;
                        @endphp
                        {!! $htmlFooter !!}
                    </div>
                </div>
            </td>
        </tr>
    </table>
</center>
