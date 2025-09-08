<x-filament-panels::page>
    {{-- Custom dashboard content --}}

    {{-- Display default dashboard widgets --}}
    @if (filled($widgets = $this->getVisibleWidgets()))
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="
                [
                    ...$this->getWidgetData(),
                    'lazy' => false,
                ]
            "
            :widgets="$widgets"
        />
    @endif

    {{-- Add any custom content here --}}
    <div class="mt-6 ">
        {{-- Your custom dashboard content goes here --}}

    </div>
</x-filament-panels::page>