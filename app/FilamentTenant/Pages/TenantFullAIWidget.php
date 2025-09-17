<?php

namespace App\FilamentTenant\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Domain\Content\Models\Content;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Domain\OpenAi\Services\OpenAiService;
use Domain\OpenAi\Context\ContentsContextBuilder;
use Domain\OpenAi\Context\BlueprintContextBuilder;
use Domain\OpenAi\Interfaces\DocumentParserInterface;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Textarea;

class TenantFullAIWidget extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $title = 'AI Widget';
    protected static ?string $slug = 'ai-widget';
    protected static string $view = 'filament-tenant.pages.ai-widget';
    protected static bool $shouldRegisterNavigation = false;

    // Make the page full width
    protected static ?string $maxWidth = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Upload & Actions')
                            ->schema([
                                FileUpload::make('file')
                                    ->label('Upload your file')
                                    ->disk('public')
                                    ->directory('uploads')
                                    ->preserveFilenames()
                                    ->imagePreviewHeight('200')
                                    ->previewable(true)
                                    ->maxSize(10240)
                                    ->required()
                                    ->helperText('Maximum file size: 10MB'),

                                Actions::make([
                                    Actions\Action::make('submit')
                                        ->color('primary')
                                        ->action('submit')
                                ]),

                                Section::make()
                                    ->schema([
                                        Actions::make([
                                            Actions\Action::make('realtime')
                                                ->label('Real-time View')
                                                ->icon('heroicon-o-eye')
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full justify-start']),
                                        ]),
                                        Actions::make([
                                            Actions\Action::make('deployment')
                                                ->label('Deployment')
                                                ->icon('heroicon-o-rocket-launch')
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full justify-start']),
                                        ]),
                                        Actions::make([
                                            Actions\Action::make('analytics')
                                                ->label('Analytics and Recommendations')
                                                ->icon('heroicon-o-chart-bar')
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full justify-start']),
                                        ]),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'h-full flex flex-col']),

                        Section::make('Logging')
                            ->schema([
                                Textarea::make('issues')
                                    ->label('Issues')
                                    ->default("Entered text in the wrong place")
                                    ->disabled()
                                    ->rows(6)
                                    ->extraAttributes(['class' => 'border-red-500 bg-red-50']),

                                Textarea::make('results')
                                    ->label('Results')
                                    ->default("No errors found yet.")
                                    ->disabled()
                                    ->rows(6)
                                    ->extraAttributes(['class' => 'border-green-500 bg-green-50']),
                            ])
                            ->columns(1)
                            ->columnSpan(1)
                            ->extraAttributes(['class' => 'h-full flex flex-col']),
                    ])
                    ->extraAttributes(['class' => 'items-stretch']),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getMaxWidth(): string
    {
        return 'full';
    }

    protected function hasLogo(): bool
    {
        return false;
    }

    public function hasTopNavigation(): bool
    {
        return false;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'hideNavigation' => true,
        ]);
    }

    public function submit()
    {
        $fileInput = $this->data['file'] ?? null;

        if (! $fileInput) {
            Notification::make()
                ->title('Please upload a file first.')
                ->danger()
                ->send();
            return;
        }

        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
        $file = collect($fileInput)->first();

        $storedPath = $file->store('uploads', 'public');
        $fullPath   = storage_path('app/public/' . $storedPath);

        $html = app(DocumentParserInterface::class)->parseToHtml($fullPath);

        // get all contents with blueprints
        $contents = Content::with('blueprint')->whereHas('blueprint')->get();

        // build combined context
        $contexts = ContentsContextBuilder::build($contents);

        // send the combined context to OpenAI at once
        $response = app(OpenAiService::class)->generateSchema($html, $contexts);

        $this->data['results'] = is_array($response)
            ? json_encode($response, JSON_PRETTY_PRINT)
            : $response;

        Notification::make()
            ->title('File processed successfully')
            ->success()
            ->send();
    }

}
