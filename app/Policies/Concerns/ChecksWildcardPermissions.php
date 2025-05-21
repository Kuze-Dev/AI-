<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

trait ChecksWildcardPermissions
{
    protected string $wildcardResourceName;

    protected function checkWildcardPermissions(User $user): bool
    {
        return $user->can("{$this->getResourceName()}.{$this->getAbility()}");
    }

    private function getResourceName(): string
    {
        return $this->wildcardResourceName ?? (string) Str::of(static::class)
            ->classBasename()
            ->remove('Policy')
            ->camel();
    }

    private function getAbility(): string
    {
        $trace = debug_backtrace(limit: 3);

        return $trace[2]['function'];
    }
}
