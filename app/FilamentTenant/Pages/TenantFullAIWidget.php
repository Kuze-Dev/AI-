<?php

namespace App\FilamentTenant\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Contracts\View\View;

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
                                    Actions\Action::make('checkErrors')
                                        ->label('✓ Check For Errors')
                                        ->action('checkErrors')
                                        ->color('primary')
                                        ->icon('heroicon-o-check-circle'),
                                ]),

                                Section::make()
                                    ->schema([
                                        TextInput::make('nav1')->default('Real-time View')->disabled(),
                                        TextInput::make('nav2')->default('Deployment')->disabled(),
                                        TextInput::make('nav3')->default('Analytics and Recommendations')->disabled(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Issues')
                                    ->schema([
                                        Textarea::make('issues')
                                            ->default("Entered text in the wrong place")
                                            ->disabled()
                                            ->rows(4),
                                    ])
                                    ->extraAttributes(['class' => 'border-red-500 bg-red-50']),

                                Section::make('Results')
                                    ->schema([
                                        Textarea::make('results')
                                            ->default("No errors found yet.")
                                            ->disabled()
                                            ->rows(4),
                                    ])
                                    ->extraAttributes(['class' => 'border-green-500 bg-green-50']),
                            ])
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function checkErrors(): void
    {
        Notification::make()
            ->title('Errors checked!')
            ->body('We analyzed your file. See Issues and Results on the right.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [];
    }

    // Override getMaxWidth method
    public function getMaxWidth(): string
    {
        return 'full';
    }

    // Add this method to hide the sidebar and topbar
    protected function hasLogo(): bool
    {
        return false;
    }

    // Hide the navigation
    public function hasTopNavigation(): bool
    {
        return false;
    }

    // Override the view data to add custom styles
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'hideNavigation' => true,
        ]);
    }
}