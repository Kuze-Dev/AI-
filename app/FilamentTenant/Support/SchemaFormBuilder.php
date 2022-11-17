<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\DataTransferObjects\SectionData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\MarkdownButton;
use Domain\Blueprint\Enums\RichtextButton;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Concerns\EntanglesStateWithSingularRelationship;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class SchemaFormBuilder extends Component
{
    use EntanglesStateWithSingularRelationship;

    protected string $view = 'forms::components.group';

    final public function __construct(string $name, SchemaData|Closure $schema)
    {
        $this->statePath($name);
        $this->schema(fn () => $this->generateFormSchema($this->evaluate($schema)));
    }

    public static function make(string $name, SchemaData|callable $schema): static
    {
        $static = app(static::class, [
            'name' => $name,
            'schema' => $schema,
        ]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan('full');
    }

    private function generateFormSchema(SchemaData $schema): array
    {
        return array_map(fn (SectionData $section) => $this->generateSectionSchema($section), $schema->sections);
    }

    private function generateSectionSchema(SectionData $section): Section
    {
        return Section::make($section->title)
            ->statePath($section->state_name)
            ->schema(array_map(fn (FieldData $field) => $this->generateFieldComponent($field), $section->fields));
    }

    private function generateFieldComponent(FieldData $field): Field
    {
        return match ($field->type) {
            FieldType::DATETIME => DateTimePicker::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->minDate($field->min)
                ->maxDate($field->max)
                ->format($field->format),
            FieldType::FILE => FileUpload::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->multiple($field->multiple)
                ->enableReordering($field->reorder)
                ->acceptedFileTypes($field->accept)
                ->maxSize($field->min_size)
                ->minSize($field->max_size)
                ->minFiles($field->min_files)
                ->maxFiles($field->max_files),
            FieldType::MARKDOWN => MarkdownEditor::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->toolbarButtons(array_map(fn (MarkdownButton $button) => $button->value, $field->buttons)),
            FieldType::RICHTEXT => RichEditor::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->toolbarButtons(array_map(fn (RichtextButton $button) => $button->value, $field->buttons)),
            FieldType::SELECT => Select::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->options(Arr::pluck($field->options, 'label', 'value')),
            FieldType::TEXTAREA => Textarea::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->minLength(fn () => $field->min_length)
                ->maxLength(fn () => $field->max_length),
            FieldType::TEXT,
            FieldType::EMAIL,
            FieldType::TEL,
            FieldType::URL,
            FieldType::PASSWORD => TextInput::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->minLength(fn () => $field->min_length)
                ->maxLength(fn () => $field->max_length),
            FieldType::NUMBER => TextInput::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules)
                ->numeric()
                ->minValue($field->min)
                ->maxValue($field->max),
            FieldType::TOGGLE => Toggle::make($field->state_name)
                ->label($field->title)
                ->required(fn () => in_array('required', $field->rules))
                ->rules($field->rules),
            default => throw new InvalidArgumentException(),
        };
    }
}
