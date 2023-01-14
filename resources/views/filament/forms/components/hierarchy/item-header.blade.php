<header
    @if ($isCollapsible) x-on:click.stop="isCollapsed = ! isCollapsed" @endif
    @class([
        'flex items-center h-10 overflow-hidden border-b bg-gray-50 rounded-t-xl',
        'dark:bg-gray-800 dark:border-gray-700' => config('forms.dark_mode'),
        'cursor-pointer' => $isCollapsible,
    ])
>
    @unless ($isItemMovementDisabled)
        <button
            title="{{ __('forms::components.repeater.buttons.move_item.label') }}"
            x-on:click.stop
            type="button"
            data-sortable-handle
            @class([
                'flex items-center justify-center flex-none w-10 h-10 text-gray-400 border-r transition hover:text-gray-500',
                'dark:border-gray-700' => config('forms.dark_mode'),
            ])
        >
            <span class="sr-only">
                {{ __('forms::components.repeater.buttons.move_item.label') }}
            </span>

            <x-heroicon-s-switch-vertical class="w-4 h-4"/>
        </button>
    @endunless

    <p @class([
        'flex-none px-4 text-xs font-medium text-gray-600 truncate',
        'dark:text-gray-400' => config('forms.dark_mode'),
    ])>
        {{ $getItemLabel($uuid, $isChild ? $parentStatePath : null) }}
    </p>

    <div class="flex-1"></div>

    <ul @class([
        'flex divide-x rtl:divide-x-reverse',
        'dark:divide-gray-700' => config('forms.dark_mode'),
    ])>
        @unless ($isItemDeletionDisabled)
            <li>
                <button
                    title="{{ __('forms::components.repeater.buttons.delete_item.label') }}"

                    wire:click.stop="dispatchFormEvent('hierarchy::deleteItem', '{{ $isChild ? $parentStatePath.'.'.$getChildrenStatePath() : $getStatePath() }}', '{{ $uuid }}')"
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
        @endunless

        @if ($isCollapsible)
            <li>
                <button
                    x-bind:title="(! isCollapsed) ? '{{ __('forms::components.repeater.buttons.collapse_item.label') }}' : '{{ __('forms::components.repeater.buttons.expand_item.label') }}'"
                    x-on:click.stop="isCollapsed = ! isCollapsed"
                    type="button"
                    class="flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-500"
                >
                    <x-heroicon-s-minus-sm class="w-4 h-4" x-show="! isCollapsed"/>

                    <span class="sr-only" x-show="! isCollapsed">
                        {{ __('forms::components.repeater.buttons.collapse_item.label') }}
                    </span>

                    <x-heroicon-s-plus-sm class="w-4 h-4" x-show="isCollapsed" x-cloak/>

                    <span class="sr-only" x-show="isCollapsed" x-cloak>
                        {{ __('forms::components.repeater.buttons.expand_item.label') }}
                    </span>
                </button>
            </li>
        @endif
    </ul>
</header>
