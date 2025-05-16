<x-filament-widgets::widget>
    <x-filament::section class="w-full max-w-full">
        <h3 class="mb-4 text-lg font-semibold">{{ $navigationGroup->getLabel() }}</h3>
        <hr class="mb-4" />
        <div class="flex flex-wrap gap-4">
            @foreach ($navigationGroup->getItems() as $key => $item)
                <x-filament::button
                    tag="a"
                    href="{{ $item->getUrl() }}"
                    class="!flex !flex-col !items-center !justify-center !space-y-2 !rounded-xl !border !border-transparent !bg-primary-600 !p-4 !text-white !shadow-sm !ring-1 !ring-transparent !transition-all !duration-200 hover:!bg-primary-500 hover:!shadow-md focus:!outline-none focus:!ring-2 focus:!ring-primary-500"
                    style="flex: 0 0 calc(20% - 1rem);"
                >
                    <x-dynamic-component
                        :component="$item->getIcon()"
                        class="h-8 w-8 text-white transition-colors duration-200 mx-auto"
                    />
                    <span class="text-sm font-medium text-white transition-colors duration-200 text-center mx-auto">
                        {{ $item->getLabel() }}
                    </span>
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
