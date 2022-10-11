<section class="space-y-8" wire:submit.prevent="confirm">

    <p class="text-center">
        {{ trans('Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    <x-filament::button class="w-full" type="button" wire:click.prevent="resendEmailVerification">
        {{ trans('Resend Verification Email') }}
    </x-filament::button>

    <div class="text-center">
        <x-tables::link href="#" wire:click.prevent="logout">{{ trans('Sign out') }}</x-table::link>
    </div>
</section>
