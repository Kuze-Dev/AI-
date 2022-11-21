<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Domain\Blueprint\DataTransferObjects\DatetimeFieldData;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\FileFieldData;
use Domain\Blueprint\DataTransferObjects\MarkdownFieldData;
use Domain\Blueprint\DataTransferObjects\RichtextFieldData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\DataTransferObjects\SectionData;
use Domain\Blueprint\DataTransferObjects\SelectFieldData;
use Domain\Blueprint\DataTransferObjects\TextareaFieldData;
use Domain\Blueprint\DataTransferObjects\TextFieldData;
use Domain\Blueprint\DataTransferObjects\ToggleFieldData;
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
        $fieldComponent = match ($field::class) {
            DatetimeFieldData::class => $this->makeDateTimePickerComponent($field),
            FileFieldData::class => $this->makeFileUploadComponent($field),
            MarkdownFieldData::class => MarkdownEditor::make($field->state_name)
                ->toolbarButtons(array_map(fn (MarkdownButton $button) => $button->value, $field->buttons)),
            RichtextFieldData::class => RichEditor::make($field->state_name)
                ->toolbarButtons(array_map(fn (RichtextButton $button) => $button->value, $field->buttons)),
            SelectFieldData::class => Select::make($field->state_name)
                ->options(Arr::pluck($field->options, 'label', 'value'))
                ->multiple($field->multiple),
            TextareaFieldData::class => $this->makeTextAreaComponent($field),
            TextFieldData::class => $this->makeTextInputComponent($field),
            ToggleFieldData::class => Toggle::make($field->state_name),
            default => throw new InvalidArgumentException(),
        };

        return $fieldComponent
            ->label($field->title)
            ->required(fn () => in_array('required', $field->rules))
            ->rules($field->rules);
    }

    private function makeDateTimePickerComponent(DatetimeFieldData $datetimeFieldData): DateTimePicker
    {
        $dateTimePicker = DateTimePicker::make($datetimeFieldData->state_name)
            ->format($datetimeFieldData->format);

        if ($datetimeFieldData->min) {
            $dateTimePicker->minDate($datetimeFieldData->min);
        }

        if ($datetimeFieldData->max) {
            $dateTimePicker->maxDate($datetimeFieldData->max);
        }

        return $dateTimePicker;
    }

    private function makeFileUploadComponent(FileFieldData $fileFieldData): FileUpload
    {
        $fileUpload = FileUpload::make($fileFieldData->state_name)
            ->getUploadedFileUrlUsing(fn (string $file) => tenant_asset($file));

        if ($fileFieldData->multiple) {
            $fileUpload->multiple($fileFieldData->multiple)
                ->minFiles($fileFieldData->min_files)
                ->maxFiles($fileFieldData->max_files);
        }

        if ($fileFieldData->reorder) {
            $fileUpload->enableReordering($fileFieldData->reorder);
        }

        if ( ! empty($fileFieldData->accept)) {
            $fileUpload->acceptedFileTypes($fileFieldData->accept);
        }

        if ($fileFieldData->min_size) {
            $fileUpload->minSize($fileFieldData->min_size);
        }

        if ($fileFieldData->max_size) {
            $fileUpload->maxSize($fileFieldData->max_size);
        }

        return $fileUpload;
    }

    private function makeTextAreaComponent(TextareaFieldData $textareaFieldData): Textarea
    {
        $textarea = Textarea::make($textareaFieldData->state_name)
            ->rows($textareaFieldData->rows)
            ->cols($textareaFieldData->cols);

        if ($textareaFieldData->min_length) {
            $textarea->minLength(fn () => $textareaFieldData->min_length);
        }

        if ($textareaFieldData->max_length) {
            $textarea->maxLength(fn () => $textareaFieldData->max_length);
        }

        return $textarea;
    }

    private function makeTextInputComponent(TextFieldData $textFieldData): TextInput
    {
        if ($textFieldData->type === FieldType::NUMBER) {
            return TextInput::make($textFieldData->state_name)
                ->numeric()
                ->minValue($textFieldData->min)
                ->maxValue($textFieldData->max);
        }

        $textInput = match ($textFieldData->type) {
            FieldType::EMAIL => TextInput::make($textFieldData->state_name)
                ->email(),
            FieldType::TEL => TextInput::make($textFieldData->state_name)
                ->tel(),
            FieldType::URL => TextInput::make($textFieldData->state_name)
                ->url(),
            FieldType::PASSWORD => TextInput::make($textFieldData->state_name)
                ->password(),
            default => TextInput::make($textFieldData->state_name),
        };

        return $textInput
            ->minLength(fn () => $textFieldData->min_length)
            ->maxLength(fn () => $textFieldData->max_length);
    }
}
