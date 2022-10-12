<form class="space-y-8" wire:submit.prevent="resetPassword">
    {{ $this->form }}

    <x-filament::button class="w-full" form="resetPassword" type="submit">
        {{ trans('Reset password') }}
    </x-filament::button>

    <div class="text-center">
        <x-tables::link href="{{ \Filament\Facades\Filament::getUrl() }}">{{ trans('Sign in') }}</x-tables::link>
    </div>
</form>
