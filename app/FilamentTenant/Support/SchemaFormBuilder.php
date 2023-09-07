<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Domain\Blueprint\DataTransferObjects\CheckBoxFieldData;
use Domain\Blueprint\DataTransferObjects\DatetimeFieldData;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\FileFieldData;
use Domain\Blueprint\DataTransferObjects\MarkdownFieldData;
use Domain\Blueprint\DataTransferObjects\MediaFieldData;
use Domain\Blueprint\DataTransferObjects\RadioFieldData;
use Domain\Blueprint\DataTransferObjects\RelatedResourceFieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
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
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SchemaFormBuilder extends Component
{
    protected string $view = 'forms::components.group';

    protected SchemaData|Closure|null $schemaData = null;

    final public function __construct(string $name, SchemaData|Closure|null $schemaData)
    {
        $this->statePath($name);
        $this->schemaData($schemaData);
    }

    public static function make(string $name, SchemaData|Closure $schemaData = null): static
    {
        $static = app(static::class, [
            'name' => $name,
            'schemaData' => $schemaData,
        ]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan('full');
    }

    public function schemaData(SchemaData|Closure $schemaData = null): self
    {
        $this->schemaData = $schemaData;

        return $this;
    }

    public function getSchemaData(): ?SchemaData
    {
        return $this->evaluate($this->schemaData);
    }

    public function getChildComponents(): array
    {
        return ($schema = $this->getSchemaData())
            ? array_map(fn (SectionData $section) => $this->generateSectionSchema($section), $schema->sections)
            : [];
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
            CheckBoxFieldData::class => CheckboxList::make($field->state_name)
                ->options(Arr::pluck($field->options, 'label', 'value'))
                ->bulkToggleable($field->bulk_toggleable),
            RadioFieldData::class => Radio::make($field->state_name)
                ->options(Arr::pluck($field->options, 'label', 'value'))
                ->inline($field->inline)
                ->descriptions(Arr::pluck($field->descriptions, 'description', 'value')),
            TextareaFieldData::class => $this->makeTextAreaComponent($field),
            TextFieldData::class => $this->makeTextInputComponent($field),
            ToggleFieldData::class => Toggle::make($field->state_name),
            RepeaterFieldData::class => $this->makeRepeaterComponent($field),
            RelatedResourceFieldData::class => $this->makeRelatedResourceComponent($field),
            MediaFieldData::class => $this->makeMediaComponent($field),
            default => throw new InvalidArgumentException('Cannot generate field component for `' . $field::class . '` as its not supported.'),
        };

        return $fieldComponent
            ->label($field->title)
            ->required(fn () => in_array('required', $field->rules))
            ->helperText($field->helper_text)
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
        $fileUpload = FileUpload::make($fileFieldData->state_name);

        if ($fileFieldData->multiple) {
            $fileUpload->multiple($fileFieldData->multiple)
                ->appendFiles()
                ->minFiles($fileFieldData->min_files)
                ->maxFiles($fileFieldData->max_files)
                ->panelLayout('grid')
                ->imagePreviewHeight('256');
        }

        if ($fileFieldData->can_download) {
            $fileUpload->enableDownload($fileFieldData->can_download);
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

    private function makeMediaComponent(MediaFieldData $mediaFieldData): FileUpload
    {
        $media = FileUpload::make($mediaFieldData->state_name);

        if ($mediaFieldData->multiple) {
            $media->multiple($mediaFieldData->multiple)
                ->appendFiles()
                ->minFiles($mediaFieldData->min_files)
                ->maxFiles($mediaFieldData->max_files)
                ->panelLayout('grid')
                ->imagePreviewHeight('256');
        }

        if ($mediaFieldData->reorder) {
            $media->enableReordering($mediaFieldData->reorder);
        }

        $media->formatStateUsing(function ($state) {
            $media = Media::where('file_name', $state)->orWhere('uuid', $state)->where('collection_name', 'blueprint_media')->first();
            if ($media) {
                return [$media->uuid];
            }
        });

        $media->getUploadedFileUrlUsing(function ($file) {
            $media = Media::where('uuid', $file)->first();
            if ($media) {
                return $media->getUrl();
            }
        });

        if ( ! empty($mediaFieldData->accept)) {
            $media->acceptedFileTypes($mediaFieldData->accept);
        }

        if ($mediaFieldData->min_size) {
            $media->minSize($mediaFieldData->min_size);
        }

        if ($mediaFieldData->max_size) {
            $media->maxSize($mediaFieldData->max_size);
        }

        return $media;
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
                ->email()
                ->rules('email:rfc,dns'),
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

    private function makeRepeaterComponent(RepeaterFieldData $repeaterFieldData): Repeater
    {
        $repeater = Repeater::make($repeaterFieldData->state_name)
            ->collapsible()
            ->schema(array_map(fn (FieldData $field) => $this->generateFieldComponent($field), $repeaterFieldData->fields));

        if ($repeaterFieldData->min) {
            $repeater->minItems($repeaterFieldData->min);
        }

        if ($repeaterFieldData->max) {
            $repeater->maxItems($repeaterFieldData->max);
        }

        return $repeater;
    }

    private function makeRelatedResourceComponent(RelatedResourceFieldData $relatedResourceFieldData): Select
    {
        $relatedResourceModelConfig = $relatedResourceFieldData->getRelatedModelConfig();
        $related = $relatedResourceFieldData->getRelatedModelInstance();
        $relatedQuery = $relatedResourceFieldData->getRelatedResourceQuery();

        $component = Select::make($relatedResourceFieldData->state_name)
            ->options($relatedQuery->pluck($relatedResourceModelConfig['title_column'], $related->getKeyName()))
            ->multiple($relatedResourceFieldData->multiple);

        if ($relatedResourceFieldData->min) {
            $component->minItems($relatedResourceFieldData->min);
        }

        if ($relatedResourceFieldData->max) {
            $component->maxItems($relatedResourceFieldData->max);
        }

        return $component;
    }
}
