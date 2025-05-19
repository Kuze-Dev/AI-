<x-filament-widgets::widget>
    <div class="grid grid-cols-2 gap-4 mb-4">
        @foreach ($navigationGroup as $navigationGroup )
        
        <div class="w-full">
            <x-filament::section class="w-full max-w-full" collapsible>
                <div class="w-full" style="min-height: 250px;">
                    <x-slot name="heading">
                        <h3 class="mb-4 text-lg font-semibold">{{ $navigationGroup->getLabel() }}</h3>
                    </x-slot>
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
                                    class="h-8 w-8 text-white transition-colors duration-200 mx-auto widget-nav-icon"
                                />
                                <span class="text-sm font-medium text-white text-center leading-tight whitespace-normal break-words flex items-center justify-center h-10 w-full max-w-[6rem] text-balance">
                                    {{ $item->getLabel() }}
                                </span>
                                
                            </x-filament::button>
                        @endforeach
                    </div>
            </div>
            </x-filament::section>
        </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
