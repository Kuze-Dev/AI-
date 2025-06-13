<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Closure;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Actions\DeleteBlockAction;
use Domain\Page\Exports\BlockExporter;
use Domain\Page\Models\Block;
use Domain\Site\Models\Site;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->string()
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('component')
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\Select::make('blueprint_id')
                    ->label(trans('Blueprint'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Blueprint::class, 'name')
                    ->disableOptionWhen(fn (?Block $record) => $record !== null)
                    ->reactive(),
                // Forms\Components\FileUpload::make('image'),
                \App\FilamentTenant\Support\MediaUploader::make('image')
                    ->dehydrateStateUsing(fn (?array $state) => array_values($state ?? []) ?: null)
                    ->formatStateUsing(function (?Block $record) {
                        if ($record) {
                            return $record->media->pluck('uuid')->toArray();
                        }

                        return [];
                    })
                    ->openable()
                    ->image()
                    ->imageEditor()
                    ->getUploadedFileUsing(function ($file) {

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

                                //                                $storage = Storage::disk(config('filament.default_filesystem_disk'));
                                //
                                //                                if ($storage->exists($file)) {
                                //                                    return $storage->url($file);
                                //                                }

                            }
                        }

                        return [];
                    }),
                // SpatieMediaLibraryFileUpload::make('image')
                //     ->image()
                //     ->collection('image')
                //     ->preserveFilenames()
                //     ->customProperties(fn (Forms\Get $get) => [
                //         'alt_text' => $get('name'),
                //     ]),
                Forms\Components\Toggle::make('is_fixed_content')
                    ->inline(false)
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('blueprint_id') ? false : true)
                    ->helperText('If enabled, the content below will serve as the default for all related pages')
                    ->reactive(),
                Forms\Components\Section::make([
                    \App\FilamentTenant\Support\CheckBoxList::make('sites')
                        ->required(fn () => \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class))
                        ->rules([
                            fn (?Block $record, \Filament\Forms\Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {

                                $siteIDs = $value;

                                if ($record) {
                                    $siteIDs = array_diff($siteIDs, $record->sites->pluck('id')->toArray());

                                    $content = Block::where('name', $get('name'))
                                        ->where('id', '!=', $record->id)
                                        ->whereHas(
                                            'sites',
                                            fn ($query) => $query->whereIn('site_id', $siteIDs)
                                        )->count();

                                } else {

                                    $content = Block::where('name', $get('name'))->whereHas(
                                        'sites',
                                        fn ($query) => $query->whereIn('site_id', $siteIDs)
                                    )->count();
                                }

                                if ($content > 0) {
                                    $fail("Block {$get('name')} is already available in selected sites.");
                                }

                            },
                        ])
                        ->options(
                            fn () => Site::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->disableOptionWhen(function (string $value, Forms\Components\CheckboxList $component) {

                            $admin = filament_admin();

                            if ($admin->hasRole(config()->string('domain.role.super_admin'))) {
                                return false;
                            }

                            $user_sites = $admin->userSite->pluck('id')->toArray();

                            $intersect = array_intersect(array_keys($component->getOptions()), $user_sites);

                            return in_array($value, $intersect, true);
                        })
                        ->formatStateUsing(fn (?Block $record) => $record ? $record->sites->pluck('id')->toArray() : []),

                ])
                    ->hidden((bool) ! (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class))),
                SchemaFormBuilder::make('data')
                    ->id('schema-form')
                    ->hidden(fn (?Block $record, \Filament\Forms\Get $get) => $get('is_fixed_content') && $record ? false : true)
                    ->schemaData(fn (\Filament\Forms\Get $get) => ($get('blueprint_id') !== null) ? Blueprint::whereId($get('blueprint_id'))->first()?->schema : null),
            ]),
        ]);

    }

    /** @throws Exception */
    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    SpatieMediaLibraryImageColumn::make('image')
                        ->collection('image')
                        ->default(
                            fn (Block $record) => $record->getFirstMedia('image') === null
                                ? 'https://via.placeholder.com/500x300/333333/fff?text=No+preview+available'
                                : null
                        )
                        ->width('100%')
                        ->height(null)
                        ->extraAttributes(['class' => ' rounded-lg w-full overflow-hidden bg-neutral-800'])
                        ->extraImgAttributes(['class' => 'aspect-[5/3] object-contain']),
                    Tables\Columns\TextColumn::make('name')
                        ->sortable()
                        ->size('lg')
                        ->weight('bold')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->size('sm')
                        ->color('gray')
                        ->dateTime()
                        ->sortable(),
                ])->space(2),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->using(function (Block $record) {
                            try {
                                return app(DeleteBlockAction::class)->execute($record);
                            } catch (DeleteRestrictedException) {
                                return false;
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ExportBulkAction::make()
                    ->label(trans('Export Selected Blocks'))
                    ->exporter(BlockExporter::class)
                    ->authorize(filament_admin()->hasRole(config()->string('domain.role.super_admin')))
                    ->withActivityLog(
                        event: 'bulk-exported',
                        description: fn (Tables\Actions\ExportBulkAction $action) => 'Bulk Exported '.$action->getModelLabel(),
                        properties: fn (Tables\Actions\ExportBulkAction $action) => [
                            'selected_record_ids' => $action->getRecords()
                                ?->map(
                                    function (int|string|Block $model): Block {
                                        if ($model instanceof Block) {
                                            return $model;
                                        }

                                        /** @phpstan-ignore return.type */
                                        return Block::whereKey($model)->first();
                                    }
                                ),
                        ]
                    ),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Resources\BlockResource\Blocks\ListBlocks::route('/'),
            'create' => Resources\BlockResource\Blocks\CreateBlock::route('/create'),
            'edit' => Resources\BlockResource\Blocks\EditBlock::route('/{record}/edit'),
        ];
    }
}
