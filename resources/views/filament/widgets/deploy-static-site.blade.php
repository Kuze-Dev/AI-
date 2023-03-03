<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
            <div>
                <dd class="text-xl font-semibold tracking-tight">
                    @lang('Deploy Static Site')
                </dd>
                <dt class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                    @lang('Build a new static site to get the recent content changes.')
                </dt>
            </div>
            <x-filament::button wire:click='deploy'>
                @lang('Deploy')
            </x-filament::button>
        </div>
    </x-filament::card>
</x-filament::widget>
