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
    @php
        $containers = $getChildComponentContainers();

        $isCollapsible = $isCollapsible();
        $isItemCreationDisabled = $isItemCreationDisabled();
        $isItemDeletionDisabled = $isItemDeletionDisabled();
        $isItemMovementDisabled = $isItemMovementDisabled();
        $hasItemLabels = $hasItemLabels();
    @endphp

    <div>
        @includeWhen($isCollapsible, 'filament.forms.components.hierarchy.collapse-actions')
    </div>

    <div {{ $attributes->merge($getExtraAttributes())->class([
        'filament-forms-hierarchy-component space-y-6 rounded-xl',
    ]) }}>
        @if (count($containers))
            <div
                class="space-y-6"
                x-init="
                    $el.sortable = new Sortable($el, {
                        group: @js($getName()),
                        fallbackOnBody: true,
                        swapThreshold: 0.5,
                        handle: '[data-sortable-handle]',
                        onSort: (event) => {
                            $wire.dispatchFormEvent('hierarchy::moveItems', '{{ $getStatePath() }}', event.target.sortable.toArray());
                        }
                    })
                "
            >
                @foreach ($containers as $uuid => $item)
                    @include('filament.forms.components.hierarchy.item')
                @endforeach
            </div>
        @endif

        @if (! $isItemCreationDisabled)
            <div class="relative flex justify-center">
                <x-forms::button
                    :wire:click="'dispatchFormEvent(\'hierarchy::createItem\', \'' . $getStatePath() . '\')'"
                    size="sm"
                    type="button"
                >
                    {{ $getCreateItemButtonLabel() }}
                </x-forms::button>
            </div>
        @endif
    </div>
</x-dynamic-component>
