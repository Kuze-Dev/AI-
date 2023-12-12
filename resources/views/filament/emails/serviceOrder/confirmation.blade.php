<center>
    <table style="padding-bottom: 20px;
    max-width: 811px;
    min-width: 220px;">
        <tr>
            <td>
                <div style="font-family: Arial, sans-serif; line-height: 1.6;border: thin solid #ddd; border-radius: 12px;padding: 40px 20px">
                    <center>
                        <div style="max-width: 600px; border-bottom: 1.3px solid #e5e7eb;">
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
                        <div style="max-width: 600px; padding: 20px;">

                            <div>
                                <h3 style="padding-bottom: 3em; font-weight: bold; color: #333;">
                                    Service Order Confirmation
                                </h3>
                            </div>

                            <p style="line-height: 1.5em;" align="left">Hi <strong>{{ $serviceOrder->customer_full_name }}</strong>,</p>

                            <p style="line-height: 1.5em;" align="left">
                                We have received your service order, <strong>{{ $serviceOrder->service_name }}</strong>, and we are
                                dedicated
                                to delivering
                                the best quality service to our valued customers.
                                However, I would like to inform you that your service request is currently pending
                                approval.
                                Our team is in the process of reviewing your requirements to ensure that we can fulfill
                                them
                                to your
                                satisfaction.
                            </p>
                        </div>
                    </center>
                    <center>
                        <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px;">
                            @php
                                $htmlFooter = $footer;
                            @endphp
                            {!! $htmlFooter !!}
                        </div>
                    </center>
                </div>
            </td>
        </tr>
    </table>
</center>
