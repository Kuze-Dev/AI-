<x-email.layouts.default>
    <div style="width: 100%;">
        <div class="header">
            @if ($logo)
                <div>
                    <img src="{{ $logo }}" alt="logo" />
                </div>
            @endif
            <div class="col-2">
                <div>
                    <span class="title">{{ $title }}</span>
                </div>
                <div><span class="subtitle">{{ $description }}</span></div>
            </div>
        </div>

        <div class="main-container">
            <div style="width: 100%; display: flex; align-items: center; justify-content: center">
                <h3 style="margin-bottom: 3em; font-weight: bold">
                    {{ $subject }}
                </h3>
            </div>

            <p style="line-height: 1.5em">Hi {{ $customer->first_name . " " . $customer->last_name }},</p>

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

            <div class="card">
                <div class="card-title">
                    <p>Summary</p>
                </div>
                <div class="summary-list horizontal-line">
                    <div>
                        <p>Billing Date</p>
                    </div>
                    <div style="text-align: right">
                        <p>
                            {{$serviceBill->bill_date->format('F d, Y')}}
                        </p>
                    </div>
                </div>

                <div class="summary-list">
                    <div>
                        <p>Amount</p>
                    </div>
                    <div style="text-align: right">
                        <p>{{ $serviceBill->serviceOrder->currency_symbol }}
                            {{ number_format((float) $serviceBill->total_amount, 2, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>
            <div style="width: 100%; display: flex; align-items: center; justify-content: center; margin-top: 3em">
                <a href={{$url}} class="button-link">Proceed to payment</button>
            </div>
        </div>

        <div class="footer">
            @php
                $htmlFooter = $footer;
            @endphp
            {!! $htmlFooter !!}
        </div>
    </div>
</x-email.layouts.app>
