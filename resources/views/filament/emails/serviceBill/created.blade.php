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
                    New Service Bill #{{$serviceBill->reference}}
                </h3>
            </div>

            <p style="line-height: 1.5em">Hi {{ $serviceBill->serviceOrder->customer_full_name }},</p>

            <p style="line-height: 1.5em">
                Thank you for being a customer. A summary of your bill is below. If you have questions, we're happy
                to help. Email < insert-email-here > or contact us through other <a href="#">support channels</a>.
            </p>

            <div class="card">
                <div class="card-title">
                    <p>Service Order Summary</p>
                </div>
                <div class="summary-list horizontal-line">
                    <div>
                        <p>{{ $serviceBill->serviceOrder->service_name }} </p>
                    </div>
                    <div style="text-align: right">
                        <p>{{ $serviceBill->serviceOrder->currency_symbol }}
                            {{ number_format((float) $serviceBill->serviceOrder->service_price, 2, '.', ',') }}
                        </p>
                    </div>
                </div>

                @if (!empty($serviceBill->additional_charges))
                <div class="summary-list horizontal-line">
                    <div>
                        <p style="margin-bottom: 2em">Additional charges</p>
                        @foreach ($serviceBill->additional_charges as $additionalCharges)
                        <p style="margin-bottom: 2em">{{$additionalCharges['name']}}</p>
                        @endforeach
                    </div>
                    <div style="text-align: center">
                        <p style="margin-bottom: 2em">&nbsp;</p>
                        @foreach ($serviceBill->additional_charges as $additionalCharges)
                        <p style="margin-bottom: 2em">{{ $additionalCharges['quantity'] }}
                            x
                        </p>
                        @endforeach
                    </div>
                    <div style="text-align: right">
                        <p style="margin-bottom: 2em">&nbsp;</p>
                        @foreach ($serviceBill->additional_charges as $additionalCharges)
                        <p style="margin-bottom: 2em">{{ $serviceBill->serviceOrder->currency_symbol }}
                            {{ number_format((float) $additionalCharges['price'], 2, '.', ',') }}
                        </p>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="summary-list ">
                    <div>
                        <p>Grand Total:</p>
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
