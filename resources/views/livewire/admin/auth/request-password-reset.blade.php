<form class="space-y-8" wire:submit.prevent="sendResetPasswordRequest">
    {{ $this->form }}

    <x-filament::button class="w-full" form="sendResetPasswordRequest" type="submit">
        {{ trans('Send email') }}
    </x-filament::button>

    <div class="text-center">
        <x-tables::link href="{{ \Filament\Facades\Filament::getUrl() }}">{{ trans('Sign in') }}</x-tables::link>
    </div>
</form>
