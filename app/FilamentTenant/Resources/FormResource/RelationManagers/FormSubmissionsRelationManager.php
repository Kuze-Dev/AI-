<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\RelationManagers;

use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Form\Models\FormSubmission;
use Filament\Forms;
use Filament\Forms\Form;
// use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $recordTitleAttribute = 'id';

    #[\Override]
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

    #[\Override]
    public function table(Table $table): Table
    {
        /** @var \Domain\Admin\Models\Admin */
        $admin = Auth::user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('data.main.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(trans('Submitted from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(trans('Submitted until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Submitted from '.Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Submitted until '.Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
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
                        function (FormSubmission $record) use ($admin) {

                            $statepaths = $record->form->blueprint->schema->getFieldStatePaths();

                            $data = [];

                            foreach ($statepaths as $key) {
                                $data[$key] = data_get($record->data, $key);
                            }

                            $data['main.submission_date'] = $record->created_at?->timezone(
                                $admin->timezone ?: config('domain.admin.default_timezone')
                            )->format('Y-m-d H:i a');

                            return $data;
                        }
                    ),

            ])
            ->defaultSort('created_at', 'desc');
    }
}
