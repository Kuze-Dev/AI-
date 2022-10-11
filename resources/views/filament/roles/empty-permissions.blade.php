<x-tables::empty-state icon="heroicon-o-x">
    <x-slot name="heading">
        @if($guard)
            No Avilable Permissions for the selected guard
        @else
            Please select a guard
        @endIf
    </x-slot>

    {{-- <x-slot name="description">
        {{ $getEmptyStateDescription() }}
    </x-slot> --}}
</x-tables::empty-state>
