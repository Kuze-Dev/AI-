<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\FilamentTenant\Resources\MediaresourceResource\Pages;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Content\Models\ContentEntry;
use Domain\Customer\Models\Customer;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\Product;
use Domain\Service\Models\Service;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Forms;
// use Filament\Resources\Form;
use Filament\Forms\Form;
use Filament\Resources\Resource;
// use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Models\MetaData;

class MediaresourceResource extends Resource
{
    // use ContextualResource;

    protected static ?string $model = Media::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    #[\Override]
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        SchemaFormBuilder::make(
                            'custom_properties',
                            fn () => Blueprint::where('id',
                                app(\App\Settings\CMSSettings::class)->media_blueprint_id)->first()?->schema
                        ),
                    ])->columnSpanFull(),
            ]);
    }

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
                    Tables\Columns\ImageColumn::make('original_url')
                        ->getStateUsing(fn ($record) => match ($record->getTypeFromMime()) {
                            'image' => $record->original_url,
                            default => 'https://dummyimage.com/600x400/000/fff&text='.$record->getTypeFromMime(),
                        })
                        ->height(200)
                        ->width('100%')
                        ->extraAttributes(['class' => 'rounded-lg w-full overflow-hidden bg-neutral-800'])
                        ->extraImgAttributes(['class' => 'aspect-[5/3] object-contain']),
                    Tables\Columns\TextColumn::make('model_type')
                        ->formatStateUsing(fn ($record) => match ($record->model_type) {
                            app(MetaData::class)->getMorphClass() => 'MetaData',
                            app(Block::class)->getMorphClass() => 'Blocks',
                            app(BlueprintData::class)->getMorphClass() => 'BlueprintData('.BlueprintData::select(['id', 'model_type'])->where('id', $record->model_id)->first()?->model_type.')',
                            default => Str::upper($record->model_type),
                        })
                        ->searchable(),
                    Tables\Columns\TextColumn::make('name')
                        ->url(fn (Media $record) => match ($record->model_type) {
                            app(MetaData::class)->getMorphClass() => self::getMetaDataResourceModel($record),
                            app(Block::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.blocks.edit', Block::find($record->model_id)),
                            app(BlueprintData::class)->getMorphClass() => self::getBlueprintDataResourceUrl($record),
                            app(Service::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.services.edit', Service::find($record->model_id)),
                            app(Customer::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.customers.edit', Customer::find($record->model_id)),
                            app(PaymentMethod::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.payment-methods.edit', PaymentMethod::find($record->model_id)),
                            app(Product::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.products.edit', Product::find($record->model_id)),
                            app(ShippingMethod::class)->getMorphClass() => self::resolveModelUrl('filament.tenant.resources.shipping-methods.edit', ShippingMethod::find($record->model_id)),
                            default => '/admin',
                        })
                        ->openUrlInNewTab()
                        ->extraAttributes(['class' => ' rounded-lg w-full overflow-hidden'])
                        ->translateLabel()
                        ->searchable()
                        ->sortable(),

                ])->space(2),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function resolveModelUrl(string $routeName, ?Model $model): string
    {
        if ($model) {
            return route($routeName, ['record' => $model]);
        }

        return '/admin';
    }

    public static function getMetaDataResourceModel(Media $media): string
    {
        $metaData = MetaData::where('id', $media->model_id)->first();

        $resource = $metaData?->resourceModel;
        if ($resource) {
            return match ($resource::class) {
                ContentEntry::class => route('filament.tenant.resources.contents.entries.edit', [
                    'ownerRecord' => $resource->content,
                    'record' => $resource,
                ]),
                Page::class => route('filament.tenant.resources.pages.edit', ['record' => $resource]),
                Product::class => route('filament.tenant.resources.products.edit', ['record' => $resource]),
                Service::class => route('filament.tenant.resources.services.edit', ['record' => $resource]),
                // TaxonomyTerm::class => route('filament.tenant.resources.taxonomies.edit', ['record' => $resource->taxonomy]),
                // Globals::class => route('filament.tenant.resources.globals.edit', ['record' => $resource]),
                default => '/admin',
            };
        }

        return '/admin';
    }
    // /** @return Model */
    // public static function getBlueprintDataResourceModel(Media $media) : Model
    // {
    //     $blueprintData = BlueprintData::where('id', $media->model_id)->first();

    //     return $blueprintData?->resourceModel;
    // }

    public static function getBlueprintDataResourceUrl(Media $media): string
    {

        $blueprintData = BlueprintData::where('id', $media->model_id)->first();

        $resource = $blueprintData?->resourceModel;
        if ($resource) {
            return match ($resource::class) {
                ContentEntry::class => route('filament.tenant.resources.contents.entries.edit', [
                    'ownerRecord' => $resource->content,
                    'record' => $resource,
                ]),
                BlockContent::class => self::resolveModelUrl('filament.tenant.resources.pages.edit', $resource->page),
                TaxonomyTerm::class => self::resolveModelUrl('filament.tenant.resources.taxonomies.edit', $resource->taxonomy),
                Globals::class => self::resolveModelUrl('filament.tenant.resources.globals.edit', $resource),
                Customer::class => self::resolveModelUrl('filament.tenant.resources.customers.edit', $resource),
                default => '/admin',
            };
        }

        return '/admin';

    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaresources::route('/'),
            'edit' => Pages\EditMediaResource::route('/{record}/edit'),
        ];
    }
}
