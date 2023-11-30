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
                Service Order Confirmation
            </h3>
        </div>

        <p style="line-height: 1.5em">Hi {{ $serviceOrder->customer_full_name }},</p>

        <p style="line-height: 1.5em">
            We have received your service order, {{ $serviceOrder->service_name }}, and we are dedicated to delivering
            the best quality service to our valued customers.
            However, I would like to inform you that your service request is currently pending approval.
            Our team is in the process of reviewing your requirements to ensure that we can fulfill them to your
            satisfaction.
        </p>
    </div>

    <div style="border-top: 1px solid #ddd; padding-top: 15px; max-width: 600px; margin: 0 auto; text-align: center">
        @php
        $htmlFooter = $footer;
        @endphp
        {!! $htmlFooter !!}
    </div>
</div>