<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div style="font-family: Arial, sans-serif; border: thin solid #ddd; border-radius: 12px;">
                    <center>
                    <div style="max-width: 600px; padding: 20px; border-bottom: 1.3px solid #e5e7eb;">
                        @if ($logo)
                            <div style="max-width: 300px;">
                                <img style="max-width: 100%; height: auto;"
                                     src="{{$logo}}"
                                     alt=""/>
                            </div>
                        @endif
                        <div style="max-width: 600px; padding: 20px" align="center">
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
                    <div style="max-width: 600px;  padding: 20px;">
                        <div align="center">
                            <h4 style="padding-bottom: 3em;">
                                Hi {{ $customer->first_name . ' ' . $customer->last_name }}, we just
                                want to inform you that your item(s) in
                                order
                                #{{ $order->reference }} has
                                been {{ $status }}.
                            </h4>
                        </div>

                        @if ($remarks)
                            <table
                                style="width: 100%; border-collapse: collapse; padding-top: 20px; padding-bottom: 40px;">
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
                    </center>

                    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px">
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
