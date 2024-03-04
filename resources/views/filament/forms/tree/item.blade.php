<div
    class="space-y-2"
    data-id="{{ $statePath }}"
    data-sortable-item
    wire:key="{{ $statePath }}"
    x-data="{
        isCollapsed: false,
        hasItems: false,
    }"
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
            <x-filament::icon
            alias="panels::topbar.global-search.field"
            icon="heroicon-o-arrows-up-down"
            {{-- icon="heroicon-m-magnifying-glass" --}}
            class="w-4 h-4"
        />
        
            {{-- <x-filament::icon icon="ellipsis-vertical" class="w-4 h-4 -mr-2"/> --}}
            {{-- <x-heroicon-o-dots-vertical class="w-4 h-4"/> --}}
        </button>

        <p @class([
            'flex-none px-4 truncate',
            'dark:text-gray-400' => config('forms.dark_mode'),
        ])>
            {{ $getItemLabel($item) }}
        </p>

        <div class="flex-1"></div>

        <ul @class([
            'flex divide-x rtl:divide-x-reverse',
            'dark:divide-gray-700' => config('forms.dark_mode'),
        ])>
            <li class="flex">
                <button
                    title="@lang('Edit')"
                    wire:click.stop="dispatchFormEvent('tree::editItem', '{{ $statePath }}')"
                    type="button"
                    @class([
                        'flex items-center justify-center flex-none w-10 h-10 text-primary-600 transition hover:text-primary-500',
                        'dark:text-primary-500 dark:hover:text-primary-400' => config('forms.dark_mode'),
                    ])
                >
                <x-filament::icon
                alias="panels::topbar.global-search.field"
                icon="heroicon-s-pencil-square"
                {{-- icon="heroicon-m-magnifying-glass" --}}
                class="w-4 h-4"
            />
                    {{-- <x-filament::icon icon="heroicon-s-pencil-alt" class="w-4 h-4"/> --}}
                </button>
                <button
                    title="{{ __('forms::components.repeater.buttons.delete_item.label') }}"
                    wire:click.stop="dispatchFormEvent('tree::deleteItem', '{{ $statePath }}')"
                    type="button"
                    @class([
                        'flex items-center justify-center flex-none w-10 h-10 text-danger-600 transition hover:text-danger-500',
                        'dark:text-danger-500 dark:hover:text-danger-400' => config('forms.dark_mode'),
                    ])
                >

                <x-filament::icon
                alias="panels::topbar.global-search.field"
                icon="heroicon-o-trash"
                class="w-4 h-4"
                />
                    {{-- <x-heroicon-s-trash class="w-4 h-4"/> --}}
                </button>
                <button
                    x-show="hasItems"
                    x-on:click.stop="isCollapsed = ! isCollapsed"
                    x-bind:class="{
                        '-rotate-180': !isCollapsed,
                    }"
                    type="button"
                    @class([
                        'flex items-center justify-center transform flex-none w-10 h-10 text-secondary-600 transition hover:text-secondary-500',
                        'dark:text-secondary-500 dark:hover:text-secondary-400' => config('forms.dark_mode'),
                    ])
                >
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </li>
        </ul>
    </div>

    <div
        @class([
            'ml-5 pl-5 border-l border-dashed border-gray-300',
            'dark:border-gray-600' => config('forms.dark_mode'),
        ])
        x-bind:class="{ 'invisible h-0 !m-0 overflow-y-hidden': isCollapsed }"
        x-bind:aria-expanded="(! isCollapsed).toString()"
    >
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
                        hasItems = event.target.sortable.toArray().length > 0;
                    }
                })

                hasItems = $el.sortable.toArray().length > 0;
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
