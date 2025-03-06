<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\DataTransferObjects\SafeDeviceData;
use Domain\Auth\DataTransferObjects\TwoFactorData;
use Domain\Auth\Exceptions\UserProviderNotSupportedException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\UnauthorizedException;
use LogicException;

class AuthenticateTwoFactorAction
{
    public function __construct(
        protected ValidateRecoveryCodeAction $recoveryCodeValidator,
        protected ValidateTotpCodeAction $totpValidator,
        protected AddSafeDeviceAction $addSafeDevice,
    ) {}

    /** @throws \Illuminate\Auth\AuthenticationException */
    public function execute(TwoFactorData $twoFactorData): bool
    {
        $user = $this->getChallengedUser($twoFactorData);

        if (! $twoFactorData->recovery_code && ! $twoFactorData->code) {
            throw new LogicException('`$twoFactorData` must provide either `$recovery_code` or `$code`.');
        }

        if ($twoFactorData->recovery_code && ! $this->recoveryCodeValidator->execute($user, $twoFactorData->recovery_code)) {
            return false;
        }

        if ($twoFactorData->code && ! $this->totpValidator->execute($user, $twoFactorData->code)) {
            return false;
        }

        Session::forget('login.id');

        Auth::guard($twoFactorData->guard)->login($user, Session::pull('login.remember', false));

        if ($twoFactorData->remember_device) {
            $this->addSafeDevice->execute(
                $user,
                new SafeDeviceData(
                    (string) Request::ip(),
                    (string) Request::userAgent(),
                )
            );
        }

        return true;
    }

    /** @throws \Illuminate\Auth\AuthenticationException */
    protected function getChallengedUser(TwoFactorData $twoFactorData): Authenticatable&TwoFactorAuthenticatable
    {
        $guard = $twoFactorData->guard ?? config('auth.defaults.guard');
        $userProvider = Auth::createUserProvider(config("auth.guards.{$guard}.provider"));

        if (! $userProvider instanceof EloquentUserProvider) {
            throw new UserProviderNotSupportedException($guard);
        }

        $user = $userProvider->retrieveById(Session::get('login.id'));

        if (! $user) {
            throw new AuthenticationException;
        }

        if (! $user instanceof TwoFactorAuthenticatable || ! $user->hasEnabledTwoFactorAuthentication()) {
            throw new UnauthorizedException;
        }

        return $user;
    }
}
