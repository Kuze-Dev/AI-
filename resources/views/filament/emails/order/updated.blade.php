<style>
    .main-container {
        text-align: center;
        padding-top: 2em;
        padding-bottom: 2em;
        padding-left: 16em;
        padding-right: 16em;
        font-size: medium;
    }

    .remarks-container {
        border: 1.5px solid #9ca3af;
        margin-top: 2em;
        padding-top: 1em;
        padding-bottom: 1em;
        padding-left: 1.3em;
        padding-right: 1.3em;
        text-align: left;
        border-radius: 0.8em;
    }

    @media (max-width: 1024px) and (min-width: 769px) {
        .main-container {
            padding-left: 8em;
            padding-right: 8em;
        }
    }

    @media (max-width: 768px) {
        .main-container {
            padding-left: 1.5em;
            padding-right: 1.5em;
        }
    }

    @media (max-width: 576px) {
        .main-container {
            padding-left: 2px;
            padding-right: 2px;
            font-size: small;
        }

        .remarks-container {
            padding: 0.8em;
        }
    }
</style>

<div style="width: 100%; font-family: Arial, Helvetica, sans-serif">
    <div style="width: 100%; max-height: 150px">
        <img src="https://img.freepik.com/free-vector/stylish-glowing-digital-red-lines-banner_1017-23964.jpg"
            alt="" style="width: 100%; height: 150px; object-fit: cover" />
    </div>

    <div class="main-container">

        <p style="line-height: 1.5em">
            Hi {{ $customer->first_name . $customer->last_name }}, we just want to inform you that your item(s) in order
            #{{ $order->reference }} has
            been {{ $status }}.
        </p>

        @if ($remarks)
            <div class="remarks-container">
                <p style="margin-bottom: 0.5em; font-weight: bold">Remarks:</p>
                <p style="line-height: 1.5em">
                    {{ $remarks }}
                </p>
            </div>
        @endif

    </div>
</div>
