<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityResource\RelationManagers;

use App\Filament\Resources\ActivityResource;
use Closure;
use Exception;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'description';

    /** @throws Exception */
    public static function form(Form $form): Form
    {
        return ActivityResource::form($form);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return ActivityResource::table($table)
            ->actions([
                ViewAction::make(),
            ]);
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }

    protected function getTableHeading(): string|Htmlable|Closure|null
    {
        return (string) Str::of($this->getOwnerRecord()::class)
            ->classBasename()
            ->headline()
            ->append(' '.static::getTitle());
    }
}
