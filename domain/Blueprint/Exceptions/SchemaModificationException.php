<?php

declare(strict_types=1);

namespace Domain\Blueprint\Exceptions;

use Exception;

class SchemaModificationException extends Exception
{
    public static function fieldTypeModified(string $stateName): self
    {
        return new self("Tried to modify field type for `{$stateName}` but is not allowed.");
    }
}
