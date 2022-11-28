<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\RelationManagers;

use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

use Illuminate\Support\Str;
use Closure;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\TextColumn;

class TaxonomyTermsRelationManager extends RelationManager
{
    protected static string $relationship = 'taxonomyTerms';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Taxonomy Term';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('name')
                        ->reactive()
                        ->afterStateUpdated(function (Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        })->required(),
                    TextInput::make('slug')->required(),
                    RichEditor::make('description')->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('created_at')->date(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('New Taxonomy Term'),
                Tables\Actions\AssociateAction::make()->disabled()->hidden(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make()->disabled()->hidden(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DissociateBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
