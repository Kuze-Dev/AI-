<x-dynamic-component
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
>
    <div
        class="flex items-center w-full gap-2 overflow-x-auto whitespace-nowrap"
        x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }"
        x-bind:class="{ 'justify-center': state }"
    >
        @foreach ($blocks as $id => $block)
            <div wire:key="block_picker.{{ $id }}" x-show="!state || {{ $id }} === state">
                <button
                    class="flex flex-col items-center justify-center flex-shrink-0 h-32 rounded-lg cursor-pointer bg-neutral-800 w-60"
                    type="button"
                    x-on:click="state = {{ $id }}"
                >
                    @if($block['image'])
                        <img class="inline-block object-contain w-full h-full" src="{{ $block['image'] }}" alt="{{ $block['name'] }} preview"/>
                    @else
                        <p class="text-sm text-white">@lang('No preview available')</p>
                    @endif
                </button>
                <p class="w-full text-sm text-center">{{ $block['name'] }}</p>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
