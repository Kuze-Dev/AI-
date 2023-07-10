<div class="space-y-2" data-id="{{ $statePath }}" data-sortable-item wire:key="{{ $statePath }}"
    x-data="{
        isCollapsed: false,
        hasItems: false,
    }">
    <li
        class="rounded-lg p-2 flex items-center justify-between w-full bg-transparent border border-gray-700">
        @if($item)
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
            <x-forms::button
                :wire:click="'dispatchFormEvent(\'productVariant::editItem\', \'' . $statePath . '\')'"
                size="sm" type="button">
                @lang('Edit')
            </x-forms::button>
        </div>
    </li>
</div>
