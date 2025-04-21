<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    {{-- :hint-action="$getHintActions()" --}}
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    @php
        $childrenStateName = $getChildrenStateName();
    @endphp

    <div {{ $attributes->merge($getExtraAttributes())->class([
        'filament-forms-tree-component space-y-6 rounded-xl',
    ]) }}>
        <div
            class="space-y-2"
            x-init="
                $el.sortable = new Sortable($el, {
                    group: @js($getName()),
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.5,
                    draggable: '[data-sortable-item]',
                    handle: '[data-sortable-handle]',
                    onSort: (event) => {
                        $wire.dispatchFormEvent('tree::moveItems', @js($getStatePath()), event.target.sortable.toArray());
                    }
                })
            "
            data-sortable-container
        >
            @foreach ($getState() as $key => $item)
                @include('filament.forms.tree.item', [
                    'key' => $key,
                    'item' => $item,
                    'statePath' => $getStatePath().'.'.$key,
                ])
            @endforeach
        </div>
        <div class="relative">
            <x-filament::button
                :wire:click="'dispatchFormEvent(\'tree::createItem\', \'' . $getStatePath() . '\')'"
                size="sm"
                type="button"
            >
                @lang('Add to :label', ['label' => lcfirst($getLabel())])
            </x-filament::button>
        </div>
    </div>
</x-dynamic-component>
