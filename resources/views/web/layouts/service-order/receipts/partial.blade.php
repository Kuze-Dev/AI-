<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Document</title>

<style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);
    * {
        font-family: "Open Sans", Arial, sans-serif;
    }
    table{
        font-size: x-small;
        margin: 0px auto;
    }
    tfoot tr td{
        font-weight: bold;
        font-size: x-small;
    }
    h2 {
        color: #2F3843;
    }
    hr {
        color: #B5BBC3;
    }
    hr {
        background: #EBEEF1;
        border: 0.5px solid #EBEEF1;
    }
    .gray {
        color: #B5BBC3;
    }
    .blue {
        color: #01648D;
    }
</style>

</head>
<body>

  <table width="90%">
    <tr>
        <td valign="top">
            <h1>{{ app(\App\Settings\SiteSettings::class)->name }}</h1>
        </td>
        <td align="right">
            <img height="100" width="100" src="{{ app(\App\Settings\SiteSettings::class)->getLogoUrl() }}" alt=""/>
        </td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    <tr>
        <td valign="top">
            <h3 class="gray">{{ trans('Receipt for') }}</h3>
            <h2>
                {{
                    $transaction->serviceOrder
                        ->customer_first_name.
                    " ".
                    $transaction->serviceOrder
                        ->customer_last_name
                }}
            </h2>
        </td>
        <td align="right">
            <h3 class="gray">{{ trans('Date') }}</h3>
            <h2>
                {{
                    $transaction->created_at
                        ->format('M. d, Y')
                }}
            </h2>
        </td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
  </table>

  <table width="90%">
    <tr>
        <td><br></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2">
            <h2 class="blue">
                {{ trans('Service Summary') }}
            </h2>
        </td>
        <td></td>
    </tr>
    <tr>
        <td><h3 class="gray">{{ trans('Description') }}</h3></td>
        <td align="right">
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td>
            <h3>
                {{ trans('Service Order Number') }}
            </h3>
        </td>
        {{-- @php
            $service_price = money(
                $transaction->serviceBill
                    ->service_price
            )
            ->multiply(100);

            $sub_total = money(
                $transaction->serviceOrder
                    ->sub_total
            )
            ->multiply(100);

            $tax_total = money(
                $transaction->serviceOrder
                    ->tax_total
            )
            ->multiply(100);

            $total_amount = money(
                $transaction->serviceBill
                    ->total_amount
            )
            ->multiply(100);
        @endphp --}}
        <td align="right">
            {{
                $transaction->serviceOrder
                    ->reference
            }}
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
        <td></td>
    </tr>
    <tr>
        <td scope="row" align="right">{{ trans('Service Name') }}</td>
        <td align="right">
            <strong>
                {{
                    $transaction->serviceOrder
                    ->service_name
                }}
            </strong>
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
        <td></td>
    </tr>
    <tr>
        <td scope="row" align="right">{{ trans('Total Balance') }}</td>
        <td align="right">
            <strong>
                {{
                    money($transaction->total_amount)->multiply(100)->add($transaction->serviceOrder->totalBalance()) 
                }}
            </strong>
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
        <td></td>
    </tr>
        <tr>
        <td scope="row" align="right">{{ trans('Amount Paid') }}</td>
        <td align="right">
            <strong>
                {{
                    money(
                        money($transaction->total_amount)->multiply(100),
                        $transaction->serviceOrder
                            ->currency_code
                    )
                        ->formatLocale()
                }}
            </strong>
        </td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
        <td></td>
    </tr>
    <tr>
        <td scope="row" align="right">{{ trans('Remaining Balance') }} </td>
        <td align="right">
            <strong>
                {{
                    $transaction->serviceOrder->totalBalance()->formatLocale(),
                }}
            </strong>
        </td>
    </tr>
    <tr>
        <td scope="row" colspan="2"><hr></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2"><br></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
  </table>

  <table width="90%">
    <tr>
        <tr>
            <td colspan="2">
                <h2 class="blue">
                    {{ trans('Payment Details') }}
                </h2>
            </td>
            <td></td>
        </tr>
        <tr>
            <td><h3 class="gray">{{ trans('Payment Method') }}</h3></td>
            <td align="right">
                <h3 class="gray">{{ trans('Amount') }}</h3>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        <tr>
            <td>
                <h3>
                    {{
                        $transaction->payment_method
                            ->title
                    }}
                </h3>
            </td>
            @php
                $total_amount = money(
                    $transaction->total_amount
                )
                ->multiply(100);
            @endphp
            <td align="right">
                <strong>
                    {{
                        money(
                            $total_amount,
                            $transaction->serviceOrder
                                ->currency_code
                        )
                            ->formatLocale()
                    }}
                </strong>
            </td>
        </tr>
        <tr>
            <td></td>
            <td align="right" class="blue"><strong>{{ trans('PAID') }}</strong></td>
        </tr>
    </tr>
    <tr>
  </table>

</body>
</html>
