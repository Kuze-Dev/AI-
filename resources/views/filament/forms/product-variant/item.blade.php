<div class="space-y-2" data-id="{{ $statePath }}" data-sortable-item wire:key="{{ $statePath }}"
    x-data="{
        isCollapsed: false,
        hasItems: false,
    }">
    <li
        class="rounded-lg p-2 flex items-center justify-between w-full bg-transparent border border-gray-700">
        <div>
            <span class="ml-2">{{ $item['combination']['size']}}</span>

            <span class="ml-2"> (SKU 121212)</span>
            <span class="ml-2"> (Stock 99)</span>
        </div>


        <div class="relative">

            <x-forms::button
                :wire:click="'dispatchFormEvent(\'productVariant::editItem\', \'' . $getStatePath() . '/' . '\')'"
                size="sm" type="button">
                @lang('Edit')
            </x-forms::button>
        </div>
    </li>
</div>
