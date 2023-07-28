<div class="space-y-2" data-id="{{ $statePath }}" data-sortable-item wire:key="{{ $statePath }}"
    x-data="{
        isCollapsed: false,
        hasItems: false,
    }">
    <li @class([
        'bg-white border p-2 border-gray-300 shadow-sm rounded-xl flex items-center justify-between h-12 cursor-pointer',
        'dark:bg-gray-800 dark:border-gray-600' => config('forms.dark_mode'),
    ])>
        @if ($item)
            <div>
                <span class="ml-2">
                    @foreach ($item['combination'] as $key => $itemOne)
                        {{ ucfirst($itemOne['option_value']) }} /
                    @endforeach
                    (SKU: {{ $item['sku'] }})
                    (Stock: {{ $item['stock'] }})
                </span>
            </div>
        <div class="relative flex items-center">
            <label class="filament-forms-field-wrapper-label mr-2 inline-flex items-center space-x-3 rtl:space-x-reverse"
                for="item.status">
                <button x-data="{ state: {{ $item['status'] }} }" role="switch" aria-checked="true" x-bind:aria-checked="state?.toString()"
                    x-on:click="state = ! state"

                    wire:click.stop="dispatchFormEvent('productVariant::toggleItem', '{{ $statePath }}')"
                    x-bind:class="{
                        'bg-primary-600': state,
                        'bg-gray-200  dark:bg-white/10 ': !state,
                    }"
                    wire:loading.attr="disabled" id="item.status"
                    dusk="filament.forms.item.status" type="button"
                    class="filament-forms-toggle-component relative inline-flex border-2 border-transparent shrink-0 h-6 w-11 rounded-full cursor-pointer transition-colors ease-in-out duration-200 outline-none disabled:opacity-70 disabled:cursor-not-allowed disabled:pointer-events-none bg-primary-600">
                    <span
                        class="pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 ease-in-out transition duration-200 translate-x-5 rtl:-translate-x-5"
                        x-bind:class="{
                            'translate-x-5 rtl:-translate-x-5': state,
                            'translate-x-0': !state,
                        }">
                        <span
                            class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-0 ease-out duration-100"
                            aria-hidden="true"
                            x-bind:class="{
                                'opacity-0 ease-out duration-100': state,
                                'opacity-100 ease-in duration-200': !state,
                            }">
                        </span>

                        <span
                            class="absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-100 ease-in duration-200"
                            aria-hidden="true"
                            x-bind:class="{
                                'opacity-100 ease-in duration-200': state,
                                'opacity-0 ease-out duration-100': !state,
                            }">
                        </span>
                    </span>
                </button>
            </label>

            <x-forms::button :wire:click="'dispatchFormEvent(\'productVariant::editItem\', \'' . $statePath . '\')'"
                size="sm" type="button">
                @lang('Edit')
            </x-forms::button>
        </div>
        @endif
    </li>
</div>
