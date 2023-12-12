<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\RelationManagers;

// use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
// use Domain\Product\Rules\UniqueProductSkuRule;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Closure;
use Domain\Product\Enums\Status;
use Domain\Product\Models\ProductVariant;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'productOptions';

    protected static ?string $recordTitleAttribute = 'Product Options';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }
}
