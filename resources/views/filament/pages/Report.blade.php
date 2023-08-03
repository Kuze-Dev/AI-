<x-filament::page class="filament-report-page">
    <div class="flex w-full justify-end items-center space-x-2">
        <label>Sort by:</label>
        <select wire:model="sortBy"
        class="filament-forms-input block rounded-lg text-gray-900 shadow-sm outline-none
        transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 
        disabled:opacity-70 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 border-gray-300
        dark:border-gray-600"
        >
            <option value="yearly">Yearly</option>
            <option value="monthly">Monthly</option>
            <option value="daily">Daily</option>
        </select>
    </div>
    <x-filament::widgets :widgets="$this->getWidgets()" :columns="$this->getColumns()"/>
</x-filament::page>

