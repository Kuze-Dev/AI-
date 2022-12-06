<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\FormResource\Pages;
use App\FilamentTenant\Resources\FormResource\RelationManagers\FormSubmissionsRelationManager;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Form\Models\Form as FormModel;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Str;
use Spatie\ValidationRules\Rules\Delimited;
use Filament\Forms\Components\Tabs;

class FormResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = FormModel::class;

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Tabs::make('Heading')->tabs([

                    Tabs\Tab::make('Main Fields')
                        ->schema([
                            Forms\Components\Card::make([
                                Forms\Components\TextInput::make('name')
                                    ->unique(ignoreRecord: true)
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->unique(ignoreRecord: true)
                                    ->rules('alpha_dash')
                                    ->disabled(fn (?FormModel $record) => $record !== null)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                Forms\Components\Select::make('blueprint_id')
                                    ->relationship('blueprint', 'name')
                                    ->saveRelationshipsUsing(null)
                                    ->required()
                                    ->exists(Blueprint::class, 'id')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->helperText(function (?FormModel $record, ?string $state) {
                                        if ($record !== null && $record->blueprint_id !== (int) $state) {
                                            return trans('Modifying the blueprint will reset all the form\'s content.');
                                        }
                                    }),

                                Forms\Components\Toggle::make('store_submission'),
                            ]),
                        ]),

                    Tabs\Tab::make(trans('Form email notifications'))
                        ->schema([
                            Forms\Components\Card::make([

                                Forms\Components\Repeater::make('formEmailNotifications')
                                    ->relationship()
                                    ->saveRelationshipsUsing(null)
                                    ->default(null)
                                    ->nullable()
                                    ->schema([
                                        Forms\Components\TextInput::make('recipient')
                                            ->label(trans('Recipient/s'))
                                            ->required()
                                            ->rule(new Delimited('email'))
                                            ->helperText('Seperated by comma'),
                                        Forms\Components\TextInput::make('cc')
                                            ->label(trans('CC/s'))
                                            ->nullable()
                                            ->rule(new Delimited('email'))
                                            ->helperText('Seperated by comma'),
                                        Forms\Components\TextInput::make('bcc')
                                            ->label(trans('BCC/s'))
                                            ->nullable()
                                            ->rule(new Delimited('email'))
                                            ->helperText('Seperated by comma'),
                                        Forms\Components\TextInput::make('reply_to')
                                            ->nullable()
                                            ->email(),
                                        Forms\Components\TextInput::make('sender')
                                            ->required()
                                            ->email(),
                                        Forms\Components\MarkdownEditor::make('template')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(5),
                            ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('blueprint.name')
                    ->sortable()
                    ->searchable()
                    ->url(fn (FormModel $record) => BlueprintResource::getUrl('edit', $record->blueprint)),
                Tables\Columns\ToggleColumn::make('store_submission')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            FormSubmissionsRelationManager::class,
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'view' => Pages\ViewForm::route('/{record}'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
