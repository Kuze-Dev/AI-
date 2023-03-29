<x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
    :hint="$getHint()" :hint-action="$getHintAction()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" x-on:state.updated="updateSlice">
        <div id="slice-picker-container" class="flex items-center gap-2 overflow-x-auto whitespace-nowrap w-full"
            x-bind:class="{ 'justify-center': state }">
            @foreach ($slices as $slice)
                <div x-show="!state || {{ $slice['id'] }} === state">
                    <button type="button"
                        class="bg-transparent hover:border-bg-white border-2 border-gray-600 cursor-pointer rounded flex-shrink-0 mb-2 flex flex-col justify-center items-center"
                        style="width: 240px; height: 136px;" x-on:click="state = {{ $slice['id'] }}">
                        <img class="h-full inline-block" src="{{ $slice['image'] }}" />
                    </button>
                    <div class="w-full text-center text-sm">{{ $slice['name'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
