<div x-data="{
    tab: null,
    init: function() {
        this.tab = @js($this->getActiveOption())
    },
    updateQueryString: function(newTab) {
        this.tab = newTab
    },
}">
    <div style="" aria-label="header" role="tablist"
        class="filament-forms-tabs-component-header flex overflow-y-auto rounded-t-xl bg-gray-100 dark:bg-gray-700">

        @foreach ($this->getTabOptions() as $option)
            <button type="button" x-on:click="updateQueryString('{{ $option }}')"
                wire:click="setActiveOption('{{ $option }}')"
                class="filament-forms-tabs-component-button flex shrink-0 items-center gap-2 p-3 text-sm font-medium filament-forms-tabs-component-button-active bg-white text-primary-600 dark:bg-gray-800"
                x-bind:class="{
                    'text-gray-500 hover:text-gray-800 focus:text-primary-600  dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-primary-600 ': tab !==
                        '{{ $option }}',
                    'filament-forms-tabs-component-button-active bg-white text-primary-600  dark:bg-gray-800 ': tab ===
                        '{{ $option }}',
                }">
                <span>{{ $option }}</span>
            </button>
        @endforeach
    </div>
</div>
