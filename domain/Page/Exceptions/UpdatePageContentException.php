<?php

declare(strict_types=1);

namespace Domain\Page\Exceptions;

use Exception;

class UpdatePageContentException extends Exception
{
    public static function publishedAtMustBeNullException(): self
    {
        return new self('Property `published_at` must null when hasPublishedAtBehavior is `false`');
    }
}
