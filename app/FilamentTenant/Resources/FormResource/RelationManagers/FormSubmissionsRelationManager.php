<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Form\Models\FormSubmission;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SchemaFormBuilder::make(
                    'data',
                    fn (FormSubmission $record) => $record->form->blueprint->schema
                ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
