<div class="px-2 pt-2">
    <div class="filament-tables-header px-2 py-2 mb-2">
        <div class="flex flex-col gap-4 md:-mr-2 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="filament-tables-header-heading text-xl font-bold tracking-tight">
                    Order Items
                </h2>
            </div>
            <div class="filament-tables-actions-container flex items-center gap-4 flex-wrap justify-end shrink-0">
                <button type="button" wire:loading.attr="disabled" wire:click="viewDetails()"
                    class="filament-button filament-button-size-md justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-200 dark:focus:text-primary-400 dark:focus:border-primary-400 dark:focus:bg-gray-800 flex items-center">

                    <span class="flex items-center gap-1">
                        <span class="">
                            View Details
                        </span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
