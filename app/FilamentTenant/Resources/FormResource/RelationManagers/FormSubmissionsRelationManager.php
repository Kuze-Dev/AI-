<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Form\Models\FormSubmission;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SchemaFormBuilder::make(
                    'data',
                    fn (FormSubmission $record) => $record->form->blueprint->schema
                ),
            ]);
    }

    public static function table(Table $table): Table
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
            ->bulkActions([
                ExportBulkAction::make()
                    ->queue()
                    ->query(fn (Builder $query) => $query)
                    ->mapUsing(
                        function ($livewire) {
                            $headers = $livewire->ownerRecord->blueprint->schema->getFieldPathLabels();

                            $headers['main.submission_date'] = 'Submission Date';

                            return $headers;
                        },
                        function (FormSubmission $record) {

                            /** @var \Domain\Admin\Models\Admin */
                            $user = Auth::user();

                            $statepaths = $record->form->blueprint->schema->getFieldStatePaths();

                            $data = [];

                            foreach ($statepaths as $key) {
                                $data[$key] = data_get($record->data, $key);
                            }

                            $data['main.submission_date'] = $record->created_at?->timezone(
                                $user->timezone
                            )->format('Y-m-d H:i a');

                            return $data;
                        }
                    ),

            ])
            ->defaultSort('created_at', 'desc');
    }
}
