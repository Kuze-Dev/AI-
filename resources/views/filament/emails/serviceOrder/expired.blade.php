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
                Service Expired
            </h3>
        </div>

        <p style="line-height: 1.5em">Hi {{ $serviceBill->serviceOrder->customer_full_name }},</p>

        <p style="line-height: 1.5em">
            Thank you for availing our service! – we hope you enjoyed your experience. Just letting you know that
            your subscription expired yesterday and you won’t be able to use our service anymore.
        </p>
        <p style="line-height: 1.5em">
            But worry no more! You can gain immediate access our service by renewing your subscription here.
        </p>

        <div style="margin: 0 auto; text-align: center; margin-bottom: 30px;">
            <a href={{$url}} style=" 
            text-decoration: none;
            padding: 10px 20px; 
            background-color: 
            #40414b; color: #fff; 
            font-weight: bold; 
            cursor: pointer;
            border: none;
            ">Renew Subscription</a>
        </div>
    </div>

    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px; margin: 0 auto; text-align: center">
        @php
        $htmlFooter = $footer;
        @endphp
        {!! $htmlFooter !!}
    </div>
</div>