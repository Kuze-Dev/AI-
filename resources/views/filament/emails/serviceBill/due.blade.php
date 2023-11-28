<div style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border-bottom: 1.3px solid #e5e7eb;">
        @if ($logo)
        <div style="text-align: center;">
            <img style="display: block; margin: 0 auto;" height="100" width="100" src={{ $logo }} alt="" />
        </div>
        @endif
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; text-align: center">
            <div>
                <span style="font-size: xx-large; font-weight: bold;">{{ $title }}</span>
            </div>
            <div><span style="color: #3f3f3f; padding-left: 4px;">{{ $description }}</span></div>
        </div>
    </div>


    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center">
            <h3 style="margin-bottom: 3em; font-weight: bold; color: #333;">
                {{ $subject }}
            </h3>
        </div>

        <p style="line-height: 1.5em">Hi {{ $serviceBill->serviceOrder->customer_full_name }},</p>

        <p style="line-height: 1.5em">
            We would like to take a moment to remind you of your upcoming
            payment due date (<strong>{{$serviceBill->due_date->format('F d, Y')}}</strong>).
        </p>

        <p style="line-height: 1.5em">
            If payment has been sent, please disregard this email. Should you have any payment inquiries?
            Please don't hesitate to contact us.
        </p>

        <p style="line-height: 1.5em">
            Thank you
        </p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 40px;">
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

        <div
            style="display: block;  margin: 0 auto; text-align: center; margin-bottom: 30px; padding: 10px 20px; background-color: #40414b; color: #fff; font-weight: bold; cursor: pointer; max-width: 200px">
            <a href={{$url}} style=" 
        text-decoration: none;
        border: none;
        color: inherit
        ">Proceed to payment</button>
        </div>
    </div>

    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px; margin: 0 auto; text-align: center">
        @php
        $htmlFooter = $footer;
        @endphp
        {!! $htmlFooter !!}
    </div>
</div>