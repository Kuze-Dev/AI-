<div
    class="space-y-6"
    data-id="{{ $item->getStatePath() }}"
    wire:key="{{ $this->id }}.{{ $item->getStatePath() }}.item"
>
    <div
        x-data="{
            isCollapsed: @js($isCollapsed()),
        }"
        x-on:hierarchy-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
        x-on:hierarchy-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"
        {{-- wire:sortable.item="{{ $uuid }}" --}}
        x-on:expand-concealing-component.window="
            error = $el.querySelector('[data-validation-error]')

            if (! error) {
                return
            }

            isCollapsed = false

            if (document.body.querySelector('[data-validation-error]') !== error) {
                return
            }

            setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'start' }), 200)
        "
        @class([
            'bg-white border border-gray-300 shadow-sm rounded-xl relative',
            'dark:bg-gray-800 dark:border-gray-600' => config('forms.dark_mode'),
        ])
    >
        @php
            $isChild = Str::contains($item->getStatePath(), $getChildrenStatePath());
            $parentStatePath = $isChild
                ? Str::beforeLast($item->getStatePath(), ".{$getChildrenStatePath()}") : null;

            $childContainers = $getChildComponentContainers(statePath: $item->getStatePath());
        @endphp

        @includeWhen(
            (! $isItemMovementDisabled) || (! $isItemDeletionDisabled) ||  $isCollapsible || $hasItemLabels,
            'filament.forms.components.hierarchy.item-header'
        )

        <div class="p-6" x-show="! isCollapsed">
            {{ $item }}
        </div>

        <div class="p-2 text-xs text-center text-gray-400" x-show="isCollapsed" x-cloak>
            {{ __('forms::components.repeater.collapsed') }}
        </div>
    </div>

    <div
        class="pl-6 space-y-6"
        x-init="
            $el.sortable =  new Sortable($el, {
                group: @js($getName()),
                fallbackOnBody: true,
                swapThreshold: 0.5,
                handle: '[data-sortable-handle]',
                onSort: (event) => {
                    $wire.dispatchFormEvent('hierarchy::moveItems', '{{ $item->getStatePath().'.'.$getChildrenStatePath() }}', event.target.sortable.toArray());
                }
            })
        "
    >
        @foreach ($childContainers as $uuid => $container)
            @include('filament.forms.components.hierarchy.item', ['item' => $container])
        @endforeach
    </div>
</div>
