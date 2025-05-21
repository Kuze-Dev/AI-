<?php

declare(strict_types=1);

namespace Domain\Internationalization\DataTransferObjects;

/**
 * FOR IDE SUPPORT
 *
 * @property array $data
 * @property array $sites
 */
class TranslationDTO
{
    public function __construct(
        public readonly ?array $data = [],

    ) {}
}
