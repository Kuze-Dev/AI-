<?php

declare(strict_types=1);

namespace Domain\Page\Exceptions;

use Exception;

class PageException extends Exception
{
    public static function publishedAtMustBeNullException(): self
    {
        return new self('Property `published_at` must null when hasPublishedAtBehavior is `false`');
    }

    public static function pastAndFutureBehaviorMustBothNullOrNotNull(): self
    {
        return new self('Property `past_behavior` and `future_behavior` must both null or not null.');
    }
}
