<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Contracts;

interface HasTabHeader
{
    public function getTabOptions(): array;

    public function getActiveOption(): string;

    public function setActiveOption(string $option): void;

    // /**
    //  * U will need this to get the table query.
    //  *
    //  * @return Builder
    //  */
    //  protected function getTableQuery(): Builder;
}
