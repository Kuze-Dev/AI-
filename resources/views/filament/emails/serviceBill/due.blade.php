<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div
                    style="font-family: Arial, sans-serif; line-height: 1.6;border: thin solid #ddd; border-radius: 12px; padding: 40px 20px">
                    <center>
                        <div style="max-width: 600px; padding: 20px; border-bottom: 1.3px solid #e5e7eb;">
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
                    <center>
                        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                            <div align="center">
                                <h3 style="padding-bottom: 3em; font-weight: bold; color: #333;">
                                    {{ $subject }}
                                </h3>
                            </div>

                            <p style="line-height: 1.5em" align="left">Hi <strong>{{ $serviceBill->serviceOrder->customer_full_name }}</strong>,</p>

                            <p style="line-height: 1.5em" align="left">
                                We would like to take a moment to remind you of your upcoming
                                payment due date (<strong>{{$serviceBill->due_date->format('F d, Y')}}</strong>).
                            </p>

                            <p style="line-height: 1.5em" align="left">
                                If payment has been sent, please disregard this email. Should you have any payment
                                inquiries?
                                Please don't hesitate to contact us.
                            </p>

                            <p style="line-height: 1.5em" align="left">
                                Thank you
                            </p>

                            <table
                                style="width: 100%; border-collapse: collapse; padding-top: 20px; padding-bottom: 40px;">
                                <tbody>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px; font-weight: bold">
                                        Summary
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px;">Amount</td>
                                    <td style=""></td>
                                    <td style="padding: 10px;">{{ $serviceBill->serviceOrder->currency_symbol }}
                                        {{ number_format((float) $serviceBill->total_amount, 2, '.', ',') }}</td>
                                </tr>
                                </tbody>
                            </table>

                            <div style="padding-bottom: 30px;">
                                <center>
                                    <a href="{{$url}}" style="
                                    padding: 10px 20px;
                                    background-color:
                                    #40414b; color: #fff;
                                    font-weight: bold;
                                    cursor: pointer;
                                    border: none;
                                    ">
                                        Proceed to payment
                                    </a>
                                </center>
                            </div>
                        </div>
                    </center>

                    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px;">
                        <center>
                            @php
                                $htmlFooter = $footer;
                            @endphp
                            {!! $htmlFooter !!}
                        </center>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</center>
