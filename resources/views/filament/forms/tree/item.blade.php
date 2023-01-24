<div
    class="space-y-2"
    data-id="{{ $statePath }}"
    data-sortable-item
    wire:key="{{ $statePath }}"
>
    <div
        @class([
            'bg-white border border-gray-300 shadow-sm rounded-xl flex items-center h-10 cursor-pointer',
            'dark:bg-gray-800 dark:border-gray-600' => config('forms.dark_mode'),
        ])
        wire:click.stop="dispatchFormEvent('tree::editItem', '{{ $statePath }}')"
    >
        <button
            type="button"
            title="{{ __('forms::components.repeater.buttons.move_item.label') }}"
            data-sortable-handle
            x-on:click.stop
            @class([
                'flex items-center justify-center flex-none w-10 h-10 text-gray-400 border-r hover:text-gray-500',
                'dark:border-gray-700' => config('forms.dark_mode'),
            ])
        >
            <span class="sr-only">
                {{ __('forms::components.repeater.buttons.move_item.label') }}
            </span>

            <x-heroicon-o-dots-vertical class="w-4 h-4 -mr-2"/>
            <x-heroicon-o-dots-vertical class="w-4 h-4"/>
        </button>

        <p @class([
            'flex-none px-4 text-xs font-medium text-gray-600 truncate',
            'dark:text-gray-400' => config('forms.dark_mode'),
        ])>
            {{ $getItemLabel($item) }}
        </p>

        <div class="flex-1"></div>

        <ul @class([
            'flex divide-x rtl:divide-x-reverse',
            'dark:divide-gray-700' => config('forms.dark_mode'),
        ])>
            <li>
                <button
                    title="{{ __('forms::components.repeater.buttons.delete_item.label') }}"
                    wire:click.stop="dispatchFormEvent('tree::deleteItem', '{{ $statePath }}')"
                    type="button"
                    @class([
                        'flex items-center justify-center flex-none w-10 h-10 text-danger-600 transition hover:text-danger-500',
                        'dark:text-danger-500 dark:hover:text-danger-400' => config('forms.dark_mode'),
                    ])
                >
                    <span class="sr-only">
                        {{ __('forms::components.repeater.buttons.delete_item.label') }}
                    </span>

                    <x-heroicon-s-trash class="w-4 h-4"/>
                </button>
            </li>
        </ul>
    </div>

    <div @class([
        'ml-4 pl-4 border-l border-dashed border-gray-300',
        'dark:border-gray-600' => config('forms.dark_mode'),
    ])>
        <div
            class="space-y-2"
            wire:key="{{ $statePath }}.children"
            x-init="
                $el.sortable = new Sortable($el, {
                    group: @js($getName()),
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.5,
                    draggable: '[data-sortable-item]',
                    handle: '[data-sortable-handle]',
                    onSort: (event) => {
                        $wire.dispatchFormEvent('tree::moveItems', @js("{$statePath}.{$childrenStateName}"), event.target.sortable.toArray());
                    }
                })
            "
        >
            @foreach ($item[$childrenStateName] ?? [] as $childKey => $child)
                @include('filament.forms.tree.item', [
                    'key' => $childKey,
                    'item' => $child,
                    'statePath' => "{$statePath}.{$childrenStateName}.{$childKey}"
                ])
            @endforeach
        </div>
    </div>
</div>
