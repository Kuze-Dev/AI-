<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Halcyon Laravel | Tall Boilerplate</title>

    <!-- Fonts -->
    {{-- <link href="https://fonts.googleapis.com/css2?family=DM+Sans&display=swap" rel="stylesheet"> --}}

    {{-- @vite('resources/css/web/app.css') --}}
</head>

<body class="bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <div class="bg-white rounded-lg shadow-lg px-8 py-10 max-w-xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <img class="h-8 w-8 mr-2"
                    src="{{ app(\App\Settings\SiteSettings::class)->logo }}"
                    alt="Logo" />
                <div class="text-gray-700 font-semibold text-lg">
                    {{ app(\App\Settings\SiteSettings::class)->name }}
                </div>
            </div>
            <div class="text-gray-700">
                <div class="font-bold text-xl mb-2">{{ trans('RECEIPT') }}</div>
                <div class="text-sm">
                    {{-- {{ trans('DATE ') . $transaction->created_at->toDateString() }} --}}
                    sad
                </div>
                <div class="text-sm">
                    {{-- {{ trans('Receipt') . '#: ' . $transaction->id }} --}}
                    sad
                </div>
            </div>
        </div>
        <div class="border-b-2 border-gray-300 pb-8 mb-8">
            <h2 class="text-2xl font-bold mb-4">Customer:</h2>
            <div class="text-gray-700 mb-2">
                {{-- {{ $transaction->serviceOrder->customer_first_name }} --}}
                zcx
            </div>
            <div class="text-gray-700 mb-2">
                {{-- {{ $transaction->serviceOrder->customer_email }} --}}
                zxc
            </div>
            <div class="text-gray-700 mb-2">
                {{-- {{ $transaction->serviceOrder->serviceBillingAddress()->fullAddress }} --}}
                zxc
            </div>
        </div>
        <table class="w-full text-left mb-8">
            <thead>
                <tr>
                    <th class="text-gray-700 font-bold uppercase py-2">
                        Service Bill Ref.
                    </th>
                    <th class="text-gray-700 font-bold uppercase py-2">
                        Amount
                    </th>
                    <th class="text-gray-700 font-bold uppercase py-2">
                        Payment Method
                    </th>
                    <th class="text-gray-700 font-bold uppercase py-2">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="py-4 text-gray-700">Product 11</td>
                    <td class="py-4 text-gray-700">1</td>
                    <td class="py-4 text-gray-700">$100.00</td>
                    <td class="py-4 text-gray-700">$100.00</td>
                </tr>
            </tbody>
        </table>
        <div class="flex justify-end mb-8">
            <div class="text-gray-700 mr-2">Subtotal:</div>
            <div class="text-gray-700">$425.00</div>
        </div>
        <div class="text-right mb-8">
            <div class="text-gray-700 mr-2">Tax:</div>
            <div class="text-gray-700">$25.50</div>

        </div>
        <div class="flex justify-end mb-8">
            <div class="text-gray-700 mr-2">Total:</div>
            <div class="text-gray-700 font-bold text-xl">$450.50</div>
        </div>
        <div class="border-t-2 border-gray-300 pt-8 mb-8">
            <div class="text-gray-700 mb-2">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint, repellendus!.
            </div>
        </div>
    </div>
</body>
</html>
