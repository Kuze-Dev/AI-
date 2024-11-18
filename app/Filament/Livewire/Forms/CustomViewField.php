<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Forms;

use Filament\Forms\Components\ViewField;
use Closure;

class CustomViewField extends ViewField
{

    // protected ?Closure $dataFilter = null;

    protected array | Arrayable | string | Closure | null $dataFilter = null;


    protected ?string $filterKey = null;

    public function dataFilter(array | Arrayable | string | Closure | null $dataFilter): static
    {
        $this->dataFilter = $dataFilter;

        return $this;
    }

    public function getdataFilter(): array
    {
        $dataFilter = $this->evaluate($this->dataFilter) ?? [];
        
        if (is_string($dataFilter) && function_exists('enum_exists') && enum_exists($dataFilter)) {
            $dataFilter = collect($dataFilter::cases())->mapWithKeys(static fn ($case) => [($case?->value ?? $case->name) => $case->name]);
        }

        if ($dataFilter instanceof Arrayable) {
            $dataFilter = $dataFilter->toArray();
        }

        return $dataFilter;
    }


    public function viewData(array | Closure $data): static
    {

        // If $data is a closure, resolve it
        if ($data instanceof Closure) {
            $data = call_user_func($data, fn (string $key) => $this->getState($key));
        }

        $filter = $this->dataFilter;
       
        // dd($this->());
        // dd($this->dataFilter);
        
        // Ensure the resulting $data is an array
        if (!is_array($data)) {
            throw new \InvalidArgumentException('The resolved view data must be an array.');
        }

        // Merge with existing view data
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    
    }
}
