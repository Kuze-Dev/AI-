<style>
    .main-container {
        text-align: center;
        padding-top: 2em;
        padding-bottom: 2em;
        padding-left: 16em;
        padding-right: 16em;
        font-size: medium;
    }

    .header {
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1.3px solid #e5e7eb;
        padding-bottom: 1em;
    }

    .header img {
        width: 100px;
        height: 100px;
        object-fit: cover
    }

    .header .col-2 {
        padding-left: 1em;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;

    }

    .title {
        font-size: xx-large;
        font-weight: bold;
    }

    .subtitle {
        color: #3f3f3f;
        padding-left: 4px;
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

        .title {
            font-size: medium;
        }

        .subtitle {
            font-size: small;
        }

        .header img {
            width: 70px;
            height: 70px;
            object-fit: cover
        }
    }
</style>

<div style="width: 100%; font-family: Arial, Helvetica, Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif">
    <div class="header">
        @if ($logo)
            <div>
                <img src={{ $logo }} alt="" />
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
