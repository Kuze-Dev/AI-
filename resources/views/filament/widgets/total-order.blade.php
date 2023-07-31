<x-filament::widget class="filament-account-widget">
    <x-filament::card class="h-full">
        <div class="text-xl font-bold">Total Order</div>
        <div class="filament-hr border-t dark:border-gray-700"></div>
        <div class="py-4">
            <div class="text-3xl font-bold">{{ $order['totalOrder'] }}</div>
            <div class="text-md text-gray-400">All Orders</div>
        </div>
        <div class="filament-hr border-t dark:border-gray-700"></div>

        <div class="grid grid-cols-2 gap-2">
            @foreach ($status as $s)
                <div class="py-4 px-2 flex items-center justify-between" style="width: 50%">
                    <div class="text-md font-bold">{{ $order['status'][strtolower($s)] }}</div>
                    <div
                        class="min-h-6 inline-flex items-center justify-center space-x-1 whitespace-nowrap 
                    rounded-xl px-2 py-0.5 text-sm font-medium tracking-tight rtl:space-x-reverse 
                    @if (strtolower($s) === 'cancelled') 
                    text-warning-700
                    bg-warning-500/10

                    @elseif (strtolower($s) === 'fulfilled')
                    text-success-700
                    bg-success-500/10

                    @elseif (strtolower($s) === 'refunded')
                    text-danger-700
                    bg-danger-500/10

                    @elseif (strtolower($s) === 'delivered')
                    text-success-500
                    bg-success-500/10

                    @elseif (strtolower($s) === 'packed')
                    text-primary-500
                    bg-primary-500/10

                    @elseif (strtolower($s) === 'shipped')
                    text-primary-700
                    bg-primary-500/10

                    @else
                      bg-success-500/10 @endif
                    ">
                        {{ ucfirst($s) }}
                    </div>

                </div>
            @endforeach
        </div>
    </x-filament::card>
</x-filament::widget>
