<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

trait HasTabHeader
{
    protected ?string $activeOption = "All";

    protected function getTableHeader(): View | Htmlable | null
    {
        return view('filament.forms.components.table-header-tabs');
    }

    public function setActiveOption(string $option): void
    {
        if ($option == 'All') {
            $this->activeOption = $option;
            return;
        }

        $this->activeOption = $option;
    }

    public function getActiveOption(): string
    {
        return $this->activeOption;
    }
}
