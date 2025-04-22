<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Forms\LocationPickerField;
use Closure;
use Domain\Blueprint\DataTransferObjects\CheckBoxFieldData;
use Domain\Blueprint\DataTransferObjects\DatetimeFieldData;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\FileFieldData;
use Domain\Blueprint\DataTransferObjects\LocationPickerData;
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
use Domain\Blueprint\DataTransferObjects\TinyEditorData;
use Domain\Blueprint\DataTransferObjects\TipTapEditorData;
use Domain\Blueprint\DataTransferObjects\ToggleFieldData;
use Domain\Blueprint\Enums\ConditionEnum;
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
use Filament\Forms\Get;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use League\Flysystem\UnableToCheckFileExistence;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SchemaFormBuilder extends Component
{
    protected string $view = 'filament-forms::components.group';

    protected SchemaData|Closure|null $schemaData = null;

    final public function __construct(string $name, SchemaData|Closure|null $schemaData)
    {
        $this->statePath($name);
        $this->reactive();
        $this->schemaData($schemaData);
    }

    public static function make(string $name, SchemaData|Closure|null $schemaData = null): static
    {
        $static = app(static::class, [
            'name' => $name,
            'schemaData' => $schemaData,
        ]);

        $static->configure();

        return $static;
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan('full');
    }

    public function schemaData(SchemaData|Closure|null $schemaData = null): self
    {
        $this->schemaData = $schemaData;

        return $this;
    }

    public function getSchemaData(): ?SchemaData
    {
        return $this->evaluate($this->schemaData);
    }

    #[\Override]
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
                ->toolbarButtons(array_map(fn (MarkdownButton $button) => $button->value, $field->buttons
                )),
            RichtextFieldData::class => $this->makeRichTextComponent($field),
            SelectFieldData::class => $this->makeSelectComponent($field),
            CheckBoxFieldData::class => $this->makeCheckBoxListComponent($field),
            RadioFieldData::class => $this->makeRadioComponent($field),
            TextareaFieldData::class => $this->makeTextAreaComponent($field),
            TextFieldData::class => $this->makeTextInputComponent($field),
            ToggleFieldData::class => Toggle::make($field->state_name),
            RepeaterFieldData::class => $this->makeRepeaterComponent($field),
            RelatedResourceFieldData::class => $this->makeRelatedResourceComponent($field),
            MediaFieldData::class => $this->makeMediaComponent($field),
            TinyEditorData::class => $this->makeTinyEditorComponent($field),
            TipTapEditorData::class => $this->makeTiptapEditorComponent($field),
            LocationPickerData::class => $this->makeLocationPickerComponent($field),

            default => throw new InvalidArgumentException('Cannot generate field component for `'.$field::class.'` as its not supported.'),
        };

        if (! $this->isDehydrated()) {
            return $fieldComponent
                ->label($field->title)
                ->helperText($field->helper_text);
        }

        return $fieldComponent
            ->label($field->title)
            ->required(fn () => in_array('required', $field->rules, true))
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
            $fileUpload->downloadable($fileFieldData->can_download);
        }

        if ($fileFieldData->reorder) {
            $fileUpload->reorderable($fileFieldData->reorder);
        }

        if (! empty($fileFieldData->accept)) {
            $fileUpload->acceptedFileTypes($fileFieldData->accept);
        }

        if ($fileFieldData->min_size) {
            $fileUpload->minSize($fileFieldData->min_size);
        }

        if ($fileFieldData->max_size) {
            $fileUpload->maxSize($fileFieldData->max_size);
        }

        if (count($fileFieldData->hidden_option)) {

            $option = $fileFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $fileUpload->reactive();
            $fileUpload->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $fileUpload;
    }

    private function makeMediaComponent(MediaFieldData $mediaFieldData): MediaUploader
    {
        $media = MediaUploader::make($mediaFieldData->state_name);

        if ($mediaFieldData->multiple) {
            $media->multiple($mediaFieldData->multiple)
                ->appendFiles()
                ->minFiles($mediaFieldData->min_files)
                ->maxFiles($mediaFieldData->max_files)
                ->panelLayout('grid')
                ->imagePreviewHeight('256');
        }

        // if ($mediaFieldData->conversions) {
        //     $media->image();
        // }

        if ($mediaFieldData->reorder) {
            $media->reorderable($mediaFieldData->reorder);
        }

        $media->openable();

        $media->formatStateUsing(function (?array $state): array {

            if ($state) {

                /** @var array */
                $media = Media::whereIn('uuid', $state)->orwhereIN('file_name', $state)->pluck('uuid')->toArray();

                if ($media) {
                    return $media;
                }
            }

            return [];
        });

        $media->dehydrateStateUsing(fn (?array $state) => array_values($state ?? []) ?: null);

        $media->getUploadedFileUsing(function ($file) {

            if (! is_null($file)) {
                $mediaModel = Media::where('uuid', $file)
                    ->orWhere('file_name', $file)
                    ->first();
                if ($mediaModel) {

                    return [
                        'name' => $mediaModel->getAttributeValue('name') ?? $mediaModel->getAttributeValue('file_name'),
                        'size' => $mediaModel->getAttributeValue('size'),
                        'type' => $mediaModel->getAttributeValue('mime_type'),
                        'url' => $mediaModel->getUrl(),
                    ];

                }

                return [];
            }
        });

        if (! empty($mediaFieldData->accept)) {
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

        if (count($textareaFieldData->hidden_option)) {

            $option = $textareaFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $textarea->reactive();
            $textarea->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
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

        if (count($textFieldData->hidden_option)) {
            $option = $textFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $textInput->reactive();
            $textInput->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $textInput
            ->minLength(fn () => $textFieldData->min_length)
            ->maxLength(fn () => $textFieldData->max_length);
    }

    private function makeRepeaterComponent(RepeaterFieldData $repeaterFieldData): Repeater
    {
        $repeater = Repeater::make($repeaterFieldData->state_name)
            ->collapsible()
            ->schema(array_map(fn (FieldData $field) => $this->generateFieldComponent($field), $repeaterFieldData->fields));

        if ($repeaterFieldData->columns) {
            $repeater->columns($repeaterFieldData->columns);
        }

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

    private function makeRichTextComponent(RichtextFieldData $richtextFieldData): RichEditor
    {
        $richEditor = RichEditor::make($richtextFieldData->state_name)
            ->toolbarButtons(
                array_map(
                    fn (RichtextButton $button) => $button->value, $richtextFieldData->buttons)
            )
            ->getUploadedAttachmentUrlUsing(function ($file) {

                $storage = Storage::disk(config()->string('filament.default_filesystem_disk'));

                try {
                    if (! $storage->exists($file)) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence) {
                    return null;
                }

                if (config()->string('filament.default_filesystem_disk') === 'r2') {
                    return $storage->url($file);
                } else {
                    if ($storage->getVisibility($file) === 'private') {
                        try {
                            return $storage->temporaryUrl(
                                $file,
                                now()->addMinutes(5),
                            );
                        } catch (\Throwable) {
                            // This driver does not support creating temporary URLs.
                        }
                    }

                    return $storage->url($file);

                }

            });

        if (count($richtextFieldData->hidden_option)) {

            $option = $richtextFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $richEditor->reactive();
            $richEditor->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $richEditor;
    }

    private function makeTinyEditorComponent(TinyEditorData $tinyEditorData): TinyEditor
    {

        $tinyEditor = TinyEditor::make($tinyEditorData->state_name)
            ->fileAttachmentsDisk(config()->string('filament.default_filesystem_disk'))
            ->fileAttachmentsVisibility('public')
            ->showMenuBar()
            ->getUploadedAttachmentUrlUsing(function ($file) {

                $storage = Storage::disk(config()->string('filament.default_filesystem_disk'));

                try {
                    if (! $storage->exists($file)) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence) {
                    return null;
                }

                if (config()->string('filament.default_filesystem_disk') === 'r2') {
                    return $storage->url($file);
                } else {
                    if ($storage->getVisibility($file) === 'private') {
                        try {
                            return $storage->temporaryUrl(
                                $file,
                                now()->addMinutes(5),
                            );
                        } catch (\Throwable) {
                            // This driver does not support creating temporary URLs.
                        }
                    }

                    return $storage->url($file);

                }
            })
            ->fileAttachmentsDirectory('tinyeditor_uploads');

        if ($tinyEditorData->min_length) {
            $tinyEditor->minLength(fn () => $tinyEditorData->min_length);
        }

        if ($tinyEditorData->max_length) {
            $tinyEditor->maxLength(fn () => $tinyEditorData->max_length);
        }

        if (count($tinyEditorData->hidden_option)) {

            $option = $tinyEditorData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $tinyEditor->reactive();
            $tinyEditor->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $tinyEditor;
    }

    public function makeTiptapEditorComponent(TiptapEditorData $tiptapEditorData): TiptapEditor
    {
        $tiptapEditor = TiptapEditor::make($tiptapEditorData->state_name)
            ->acceptedFileTypes($tiptapEditorData->accept)
            ->tools(
                $tiptapEditorData->tools
            )
            ->directory('attachments')
            ->extraInputAttributes(['style' => 'min-height: 12rem;'])
            ->maxContentWidth('full')
            ->output(\FilamentTiptapEditor\Enums\TiptapOutput::Html); // optional, change the format for saved data, default is html

        if (count($tiptapEditorData->hidden_option)) {

            $option = $tiptapEditorData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $tiptapEditor->reactive();
            $tiptapEditor->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $tiptapEditor;
    }

    public function makeCheckBoxListComponent(CheckBoxFieldData $checkBoxFieldData): CheckboxList
    {
        $checkboxlist = CheckboxList::make($checkBoxFieldData->state_name)
            ->options(Arr::pluck($checkBoxFieldData->options, 'label', 'value'))
            ->bulkToggleable($checkBoxFieldData->bulk_toggleable);

        if (count($checkBoxFieldData->hidden_option)) {

            $option = $checkBoxFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $checkboxlist->reactive();
            $checkboxlist->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $checkboxlist;
    }

    public function makeSelectComponent(SelectFieldData $selectFieldData): Select
    {

        $select = Select::make($selectFieldData->state_name)
            ->options(Arr::pluck($selectFieldData->options, 'label', 'value'))
            ->multiple($selectFieldData->multiple);

        if (count($selectFieldData->hidden_option)) {

            $option = $selectFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $select->reactive();
            $select->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $select;

    }

    public function makeRadioComponent(RadioFieldData $radioFieldData): Radio
    {

        $radio = Radio::make($radioFieldData->state_name)
            ->options(Arr::pluck($radioFieldData->options, 'label', 'value'))
            ->inline($radioFieldData->inline)
            ->descriptions(Arr::pluck($radioFieldData->descriptions, 'description', 'value'));

        if (count($radioFieldData->hidden_option)) {

            $option = $radioFieldData->hidden_option['0'];
            $enum = ConditionEnum::tryFrom($option['condition']);

            $radio->reactive();
            $radio->hidden(function (Get $get) use ($enum, $option) {
                if ($enum) {
                    return $enum->evaluate($get($option['base_state_name']), $option['value']);
                }

                return false;
            });
        }

        return $radio;

    }

    public function makeLocationPickerComponent(LocationPickerData $locationPickerData): LocationPickerField
    {

        return LocationPickerField::make($locationPickerData->state_name);

    }
}
