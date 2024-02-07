<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use App\FilamentTenant\Resources\LocaleResource\Pages\ListLocale;
use Domain\Internationalization\Models\Locale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LocaleResource extends Resource
{
    protected static ?string $model = Locale::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return trans('CMS');
    }

    /** @throws FileNotFoundException */
    public static function form(Form $form): Form
    {
        /** @var array<string, array> $locales_json */
        $locales_json = json_decode(File::get(base_path('locales.json')), true);
        $locales = collect($locales_json);

        $options = $locales->map(function ($locale) {
            $display = "{$locale['locale']} ({$locale['code']})";

            return [$display => $display];
        })->collapse();

        return $form->schema([
            Forms\Components\Select::make('name')
                ->options($options)
                ->searchable()
                ->lazy()
                ->unique(ignoreRecord: true)
                ->afterStateUpdated(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set, $state) {
                    if ($get('name') === $state || blank($get('name'))) {
                        $code = preg_replace('/.*\((.*)\)/', '$1', $state);
                        $set('code', $code);
                    }
                })
                ->required(),
            Forms\Components\TextInput::make('code')
                ->unique(ignoreRecord: true)
                ->dehydrateStateUsing(fn (\Filament\Forms\Get $get, $state) => $state ?: $get('code'))
                ->disabled()
                ->required(),
            Forms\Components\Checkbox::make('is_default')
                ->label('Set Default')
                ->hint('One default locale is required, change it by selecting another one'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\CheckboxColumn::make('is_default')->label('Default')->disabled(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(timezone: Auth::user()?->timezone),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(timezone: Auth::user()?->timezone),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->is_default),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->is_default),
                ]),

            ])
            ->defaultSort('is_default', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLocale::route('/'),
        ];
    }
}
