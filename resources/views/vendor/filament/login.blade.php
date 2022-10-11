<form class="space-y-8" wire:submit.prevent="authenticate">
    {{ $this->form }}

    <x-filament::button class="w-full" form="authenticate" type="submit">
        {{ trans('filament::login.buttons.submit.label') }}
    </x-filament::button>

    <div class="text-center">
        <x-tables::link href="{{ route('admin.password.request') }}">{{ trans('Forgot password?') }}</x-table::link>
    </div>
</form>
