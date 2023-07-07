<x-filament::page class="filament-report-page">
    <div class="flex w-full justify-end">
        <button class="px-6 py-2 text-white bg-primary-500 rounded-xl">Download Reports</button>
    </div>
    <x-filament::widgets :widgets="$this->getWidgets()" :columns="$this->getColumns()" />
</x-filament::page>
