
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
            <h4 style="margin-bottom: 3em; font-weight: bold">
                Hi {{ $customer->first_name . ' ' . $customer->last_name }}, we just want to inform you that your item(s) in
            order
            #{{ $order->reference }} has
            been {{ $status }}.
            </h4>
        </div>

        @if ($remarks)
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 40px;">
            <tbody>
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 10px; font-weight: bold">
                        Remarks
                    </td>
                </tr>
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 10px;">{{ $remarks }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px; margin: 0 auto; text-align: center">
        @php
        $htmlFooter = $footer;
        @endphp
        {!! $htmlFooter !!}
    </div>
</div>
