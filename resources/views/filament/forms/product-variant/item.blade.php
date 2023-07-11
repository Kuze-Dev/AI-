<div class="space-y-2" data-id="{{ $statePath }}" data-sortable-item wire:key="{{ $statePath }}"
    x-data="{
        isCollapsed: false,
        hasItems: false,
    }">
    <li @class([
        'bg-white border p-2 border-gray-300 shadow-sm rounded-xl flex items-center justify-between h-12 cursor-pointer',
        'dark:bg-gray-800 dark:border-gray-600' => config('forms.dark_mode'),
    ])
    >
        @if ($item)
            <div>
                <span class="ml-2">
                    @foreach ($item['combination'] as $key => $itemOne)
                        {{ ucfirst($item['combination'][$key]) }} /
                    @endforeach
                    (SKU: {{ $item['sku'] }})
                    (Stock: {{ $item['stock'] }})
                </span>
            </div>
        @endif
        <div class="relative">
            {{-- <div class="relative inline-block w-10 mr-2 align-middle select-none">
                <input type="checkbox" name="toggle" id="toggle"
                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer">
                <label for="toggle"
                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
            </div> --}}

            <x-forms::button :wire:click="'dispatchFormEvent(\'productVariant::editItem\', \'' . $statePath . '\')'"
                size="sm" type="button">
                @lang('Edit')
            </x-forms::button>
        </div>
    </li>
</div>
