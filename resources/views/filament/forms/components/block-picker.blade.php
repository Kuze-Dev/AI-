{{-- <x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
> --}}
@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :field="$field"
>

    <div
        class="flex items-center w-full gap-2 overflow-x-auto whitespace-nowrap"
        x-data="{ 
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            blocks: @js($blocks),
            showModal: false, 
            blockEvent: function(i) {
                if (this.state) {
                    this.showModal = ! this.showModal;
                }
                this.state = i
            }
        }"
        x-bind:class="{ 'justify-center': state }"
    >

        <div x-show="showModal" class="w-full fixed top-0 right-0 h-full flex items-center justify-center z-10" style="background-color: #00000066;">
            <div @click.outside="showModal = false" class="relative" style="background-color: #262626; border-radius: 10px; box-shadow: 0px 0px 4px 1px #494949; padding-top:40px; padding-bottom:20px;">
                <button type="button" class="absolute p-10 font-bolder cursor-pointer text-2xl text-white" 
                    @click="showModal = false"
                    style="right:20px; top:5px;">X</button>
                <button
                    class="flex flex-col items-center justify-center cursor-pointer h-36 w-60"
                    type="button"
                    style="min-width: 750px; min-height: 550px;">
                    <img class="inline-block object-contain w-full h-full" :src="blocks[state]['image']" alt="preview"/>
                </button>
            </div>
        </div>

        @foreach ($blocks as $id => $block)
            <div wire:key="{{ $getId() }}.{{ $id }}" x-show="!state || {{ $id }} === state">
                <button
                    class="flex flex-col items-center justify-center flex-shrink-0 rounded-lg cursor-pointer h-36 bg-neutral-800 w-60"
                    type="button"
                    @click="blockEvent({{$id}})"
                >
                    @if($block['image'])
                        <img class="inline-block object-contain w-full h-full" src="{{ $block['image'] }}" alt="{{ $block['name'] }} preview"/>
                    @else
                        <p class="text-sm text-white">@lang('No preview available')</p>
                    @endif
                </button>
                <p class="w-full text-sm text-center py-2">{{ $block['name'] }}</p>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
