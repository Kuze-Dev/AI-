<section class="space-y-8">
    <p class="text-center">
        {{ trans('Your account has been deactivated. Please contact your administrator to activate your account.') }}
    </p>

    <div class="text-center">
        <x-tables::link href="#" wire:click.prevent="logout">{{ trans('Sign out') }}</x-tables::link>
    </div>
</section>
