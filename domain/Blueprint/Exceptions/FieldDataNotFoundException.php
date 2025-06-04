<?php

declare(strict_types=1);

namespace Domain\Blueprint\Exceptions;

use Exception;

class FieldDataNotFoundException extends Exception
{
    public static function fieldStateName(string $stateName): self
    {
        return new self("Tried to find `{$stateName}` but not found on the blueprint.");
    }
}
