<x-filament::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach ($settings as $setting)
            @if (!$setting::shouldShowSettingsCard())
                @continue
            @endif
            <x-filament::card class="col-span-1">
                <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                    <x-filament::card.heading class="inline-flex items-center space-x-3 rtl:space-x-reverse">
                        <x-dynamic-component class="h-6" :component="$setting::getNavigationIcon()" />
                        <span>
                            {{ $setting::getNavigationLabel() }}
                        </span>
                    </x-filament::card.heading>
                    <x-filament::button href="{{ $setting::getUrl() }}" tag="a">
                        {{ trans('Update') }}
                    </x-filament::button>
                </div>
            </x-filament::card>
        @endforeach
    </div>
</x-filament::page>
