<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\HasActiveState;
use Domain\Auth\Events\Activated;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class ActivateAccountAction
{
    public function execute(User&HasActiveState $user): ?bool
    {
        if ($user->isActive()) {
            return null;
        }

        $user->forceFill(['active' => true])
            ->save();

        Event::dispatch(new Activated($user));

        return true;
    }
}
