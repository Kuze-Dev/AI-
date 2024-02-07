<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div
                    style="font-family: Arial, sans-serif; line-height: 1.6;border: thin solid #ddd; border-radius: 12px;padding: 40px 20px">
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
                                <div><span style="color: #3f3f3f; padding-left: 4px;">{{ $description }}</span></div>
                            </div>
                        </div>
                    </center>
                    <center>
                        <div style="max-width: 600px;  padding: 20px;">
                            <div>
                                <h3 style="padding-bottom: 3em; font-weight: bold; color: #333;">
                                    Service Expired
                                </h3>
                            </div>

                            <p style="line-height: 1.5em" align="left">Hi
                                <strong>{{ $serviceBill->serviceOrder->customer_full_name }}</strong>,</p>

                            <p style="line-height: 1.5em" align="left">
                                Thank you for availing our service! – we hope you enjoyed your experience. Just letting
                                you know that
                                your subscription expired yesterday and you won’t be able to use our service anymore.
                            </p>
                            <p style="line-height: 1.5em" align="left">
                                But worry no more! You can gain immediate access our service by renewing your
                                subscription here.
                            </p>

                            <div style="padding-top: 30px;">
                                <a href="{{$url}}" style="
            padding: 10px 20px;
            background-color:
            #40414b; color: #fff;
            font-weight: bold;
            cursor: pointer;
            border: none;
            ">Renew Subscription</a>
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
