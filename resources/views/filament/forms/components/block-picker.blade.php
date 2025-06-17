
@php
    $statePath = $getStatePath();
    $stateBinding = $applyStateBindingModifiers("\$entangle('{$statePath}')");
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :field="$field"
>
<div style="margin:auto !important">
<x-filament::modal slide-over id="block-selection-modal" style="margin:auto">

<x-slot name="trigger">
    <div class="flex justify-center">
        <x-filament::button class="p-2 flex flex-col items-center justify-center space-y-2 border" style="background-color: transparent !important;">
            @if (isset($blocks[$getState()]['image']))
                @if ($blocks[$getState()]['image'])
                    <img class="object-contain" style="height: 200px"
                         src="{{ $blocks[$getState()]['image'] }}"
                         alt="{{ $blocks[$getState()]['name'] }} preview" />
                         <br/>
                    <span class="text-sm text-white text-center">
                        {{ $blocks[$getState()]['name'] }}
                    </span>
                @else
                    <p class="text-sm text-white">@lang('No preview available')</p>
                @endif
            @else
                <p class="text-sm text-white">@lang('Select a '){{ $getLabel() }}</p>
            @endif
        </x-filament::button>
    </div>
</x-slot>


<x-slot name="heading">
    <div class="flex items-center justify-between">
        <h2 class="text-md font-semibold text-gray-900 dark:text-gray-100">
            @lang('Select a '){{ $getLabel() }}
        </h2>
       
    </div>
</x-slot>

<x-slot name="description">
    <div
        x-data="{
            state: $wire.{{ $stateBinding }},
            blockEvent(i) {
                this.state = i;
                Livewire.dispatch('close-modal', { id: 'block-selection-modal' });
            }
        }"
        class="flex flex-wrap gap-4 pt-5 mt-5"
    >
        @php
            $block_ids = $getdataFilter();
        @endphp

        @foreach ($blocks as $id => $block)
            @if (count($block_ids) > 0 && is_null($getState()))
                    @if (in_array($id,$block_ids) && \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class))
                        <div wire:key="{{ $getId() }}.{{ $id }}">
                            <button
                                type="button"
                                @click="blockEvent({{ $id }})"
                                class="flex flex-col items-center justify-center flex-shrink-0 rounded-lg cursor-pointer h-36 bg-neutral-800 w-60"
                            >
                                @if($block['image'])
                                    <img class="inline-block object-contain w-full h-full" src="{{ $block['image'] }}" alt="{{ $block['name'] }} preview"/>
                                @else
                                    <p class="text-sm text-white">@lang('No preview available')</p>
                                @endif
                            </button>
                            <p class="w-full text-sm text-center py-2">{{ $block['name'] }}</p>
                        </div>
                    @endif
                @elseif(\Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\SitesManagement::class) && is_null($getState()) )
                    <div wire:key="{{ $getId() }}.{{ $id }}">
                        <button
                            type="button"
                            @click="blockEvent({{ $id }})"
                            class="flex flex-col items-center justify-center flex-shrink-0 rounded-lg cursor-pointer h-36 bg-neutral-800 w-60"
                        >
                            @if($block['image'])
                                <img class="inline-block object-contain w-full h-full" src="{{ $block['image'] }}" alt="{{ $block['name'] }} preview"/>
                            @else
                                <p class="text-sm text-white">@lang('No preview available')</p>
                            @endif
                        </button>
                        <p class="w-full text-sm text-center py-2">{{ $block['name'] }}</p>
                    </div>
                @elseif(!is_null($getState()) && $id == $getState())
                    <div wire:key="{{ $getId() }}.{{ $id }}">
                        <button
                            type="button"
                            @click="blockEvent({{ $id }})"
                            class="flex flex-col items-center justify-center flex-shrink-0 rounded-lg cursor-pointer h-36 bg-neutral-800 w-60"
                        >
                            @if($block['image'])
                                <img class="inline-block object-contain w-full h-full" src="{{ $block['image'] }}" alt="{{ $block['name'] }} preview"/>
                            @else
                                <p class="text-sm text-white">@lang('No preview available')</p>
                            @endif
                        </button>
                        <p class="w-full text-sm text-center py-2">{{ $block['name'] }}</p>
                    </div>
                    <small>Note: Changing blocks after selecting is not allowed. remove the block if you wish to change it.</small>
            @endif
                <hr/>
        @endforeach
    </div>
</x-slot>
</x-filament::modal>
</div>
</x-dynamic-component>
