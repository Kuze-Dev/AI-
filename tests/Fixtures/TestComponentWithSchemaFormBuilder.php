<?php

namespace Tests\Fixtures;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class TestComponentWithSchemaFormBuilder extends Component implements HasForms
{
    use InteractsWithForms;

    protected static SchemaData $schema;

    public $data;

    public static function setSchema(SchemaData $schema): void
    {
        self::$schema = $schema;
    }

    public function getFormSchema(): array
    {
        return [
            SchemaFormBuilder::make('data', fn () => self::$schema),
        ];
    }

    public function render(): string
    {
        return <<<'blade'
                {{ $this->form }}
            blade;
    }
}
