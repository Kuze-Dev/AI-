<form class="space-y-8" wire:submit.prevent="confirm">
    {{ $this->form }}

    <x-filament::button class="w-full" form="confirm" type="submit">
        {{ trans('Confirm') }}
    </x-filament::button>
    
    <div class="text-center">
        <x-tables::link href="{{ url()->previous() }}">{{ trans('Go back') }}</x-table::link>
    </div>
</form>
