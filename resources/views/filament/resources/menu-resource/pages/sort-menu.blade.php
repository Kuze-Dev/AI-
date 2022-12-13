{{-- <x-filament::page>
    <x-filament::form wire:submit.prevent="save">
        @foreach ($record->schema as $key => $schema)
            <x-filament::card>
                <x-filament::card.heading>
                    {{ $schema['title'] }}
                </x-filament::card.heading>
            </x-filament::card>
            @foreach ($schema['child'] as $key => $child)
                <div style="padding-left:50px">
                    <x-filament::card>
                        {{ $child['title'] }}
                    </x-filament::card>
                </div>
                @foreach ($child['child'] as $key => $child2)
                    <div style="padding-left:100px">
                        <x-filament::card>
                            {{ $child2['title'] }}
                        </x-filament::card>
                    </div>
                @endforeach
            @endforeach
        @endforeach
        <x-filament::form wire:submit.prevent="save">
</x-filament::page> --}}
<x-filament::page :widget-data="['record' => $record]" :class="\Illuminate\Support\Arr::toCssClasses([
    'filament-resources-edit-record-page',
    'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
    'filament-resources-record-' . $record->getKey(),
])">
    <x-filament::form wire:submit.prevent="save">
        @foreach ($record->schema as $key => $schema)
            <x-filament::card>
                <x-filament::card.heading>
                    {{ $schema['title'] }}
                </x-filament::card.heading>
            </x-filament::card>
            @foreach ($schema['child'] as $key => $child)
                <div style="padding-left:50px">
                    <x-filament::card>
                        {{ $child['title'] }}
                    </x-filament::card>
                </div>
                @foreach ($child['child'] as $key => $child2)
                    <div style="padding-left:100px">
                        <x-filament::card>
                            {{ $child2['title'] }}
                        </x-filament::card>
                    </div>
                @endforeach
            @endforeach
        @endforeach
        <x-filament::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament::form>
</x-filament::page>
