<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Forms;

use Filament\Forms\Components\ViewField;
use Closure;

class CustomViewField extends ViewField
{

    public function viewData(array | Closure $data): static
    {

        // If $data is a closure, resolve it
        if ($data instanceof Closure) {
            $data = call_user_func($data, fn (string $key) => $this->getState($key));
        }
        dd($data);
        // Ensure the resulting $data is an array
        if (!is_array($data)) {
            throw new \InvalidArgumentException('The resolved view data must be an array.');
        }

        // Merge with existing view data
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    
    }
}
