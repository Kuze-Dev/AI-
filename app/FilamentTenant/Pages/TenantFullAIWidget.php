<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use Domain\Content\Actions\CreateContentEntryAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\OpenAi\Context\ContentsContextBuilder;
use Domain\OpenAi\Interfaces\DocumentParserInterface;
use Domain\OpenAi\Services\OpenAiService;
use Domain\Site\Models\Site;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TenantFullAIWidget extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $title = 'AI Widget';

    protected static ?string $slug = 'ai-widget';

    protected static string $view = 'filament-tenant.pages.ai-widget';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

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
                                        ->action('submit'),
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
                                                ->extraAttributes(['class' => 'w-full justify-start'])
                                                ->url(route('filament.tenant.pages.deployment')),
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
                            ->schema([Textarea::make('issues')
                                ->label('Issues')
                                ->default('Entered text in the wrong place')
                                ->disabled()
                                ->rows(6)
                                ->extraAttributes([
                                    'style' => 'border: 1px solid red; box-shadow: none;',
                                ]),

                                Textarea::make('results')
                                    ->label('Results')
                                    ->default('No errors found yet.')
                                    ->disabled()
                                    ->rows(6)
                                    ->extraAttributes([
                                        'style' => 'border: 1px solid green; box-shadow: none;',
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpan(1)
                            ->extraAttributes(['class' => 'h-full flex flex-col']),
                    ])
                    ->extraAttributes(['class' => 'items-stretch']),
            ])
            ->statePath('data');
    }

    public function submit(): ?RedirectResponse
    {
        $fileInput = $this->data['file'] ?? null;

        if (! $fileInput) {
            Notification::make()
                ->title('Please upload a file first.')
                ->danger()
                ->send();

            return null;
        }

        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
        $file = collect($fileInput)->first();
        $storedPath = $file->store('uploads', 'public');
        $fullPath = storage_path('app/public/'.$storedPath);
        $html = app(DocumentParserInterface::class)->parseToHtml($fullPath);
        $contents = Content::with('blueprint')->whereHas('blueprint')->get();
        $contexts = ContentsContextBuilder::build($contents);
        $response = app(OpenAiService::class)->generateSchema($html, $contexts);

        $publishedAt = now();
        $content = Content::findOrFail($response['additional_data']['content_id']);

        // Sites
        $siteIDs = (! empty($this->data['sites']))
            ? Site::whereIn('domain', explode(',', $this->data['sites']))->pluck('id')->toArray()
            : [];

        // Taxonomy
        $taxonomyIds = (! empty($this->data['taxonomy_terms']))
            ? TaxonomyTerm::whereIn('slug', explode(',', $this->data['taxonomy_terms']))->pluck('id')->toArray()
            : [];

        // Build the entry data from the response
        $contentEntryData = ContentEntryData::fromArray([
            'title' => $response['additional_data']['title'],
            'locale' => $this->data['locale'] ?? null,
            'route_url' => [
                'url' => $response['additional_data']['route_url']
                    ?? '/'.$content->slug.'/'.Str::slug($response['additional_data']['title'] ?? 'untitled'),
                'is_override' => isset($response['additional_data']['route_url']),
            ],
            'author_id' => filament_admin()->id,
            'published_at' => $publishedAt,
            'status' => ! empty($this->data['status']),
            'meta_data' => [
                'title' => $response['metadata']['title'] ?? $response['additional_data']['title'] ?? '',
                'description' => $response['metadata']['description'] ?? '',
                'keywords' => $response['metadata']['keywords'] ?? '',
            ],
            'data' => $response['data'] ?? [],
            'sites' => $siteIDs,
            'taxonomy_terms' => $taxonomyIds,
        ]);

        try {
            $contentEntry = app(CreateContentEntryAction::class)
                ->execute($content, $contentEntryData);

            $redirect = $this->redirectRoute(
                'filament.tenant.resources.contents.entries.edit',
                [
                    'ownerRecord' => $content,
                    'record' => $contentEntry,
                ]
            );

            Notification::make()
                ->title('File processed successfully')
                ->success()
                ->send();

            return $redirect;

        } catch (\Throwable $th) {
            // Delete the latest content entry related to this content
            $latestEntry = $content->contentEntries()->latest('id')->first();
            if ($latestEntry) {
                $latestEntry->delete();
            }

            Notification::make()
                ->danger()
                ->title(trans('Import Error'))
                ->body(trans(
                    'There was an error while importing the content entry on :entry_title , '.$th->getMessage(),
                    ['entry_title' => $response['additional_data']['title']]
                ))
                ->sendToDatabase(filament_admin());

            return null;
        }
    }
}
