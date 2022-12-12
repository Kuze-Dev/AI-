<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\RelationManagers;

use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;
use Closure;
use Domain\Taxonomy\Actions\CreateTaxonomyTermAction;
use Domain\Taxonomy\Actions\UpdateTaxonomyTermAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

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
                        ->afterStateUpdated(function (?TaxonomyTerm $record, Closure $set, $state) {
                            if ($record === null) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('slug')
                        ->required()
                        ->disabled(fn (?TaxonomyTerm $record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->rules('alpha_dash'),
                    MarkdownEditor::make('description'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('order')->sortable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('created_at')->date(),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (self $livewire, array $data) {
                        return DB::transaction(function () use ($livewire, $data) {
                            /** @var Taxonomy $taxonomy */
                            $taxonomy = $livewire->getOwnerRecord();

                            return app(CreateTaxonomyTermAction::class)
                                ->execute($taxonomy, new TaxonomyTermData(...$data));
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (self $livewire, TaxonomyTerm $record, array $data) {
                        return DB::transaction(
                            fn () => app(UpdateTaxonomyTermAction::class)
                                ->execute(
                                    $record,
                                    new TaxonomyTermData(...$data)
                                )
                        );
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
