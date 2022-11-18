<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Enums\PageBehavior;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getFormSchema(): array
    {
        $behaviors = collect(PageBehavior::cases())
            ->mapWithKeys(fn (PageBehavior $fieldType) => [
                $fieldType->value => Str::headline($fieldType->value),
            ]);

        return [
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('blueprint_id')
                    ->relationship('blueprint', 'name')
                    ->required()
                    ->exists(Blueprint::class, 'id')
                    ->searchable()
                    ->preload(),
                Forms\Components\Card::make([
                    Forms\Components\Toggle::make('published_dates')
                        ->reactive(),
                    Forms\Components\Section::make('Behavior')
                        ->schema([
                            Forms\Components\Select::make('past_behavior')
                                ->required()
                                ->enum(PageBehavior::class)
                                ->options($behaviors),

                            Forms\Components\Select::make('future_behavior')
                                ->required()
                                ->enum(PageBehavior::class)
                                ->options($behaviors),
                        ])
                        ->when(fn (array $state) => $state['published_dates'])
                        ->columns(),
                ]),
            ]),
        ];
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            function () use ($data) {
                if ($data['published_dates']) {
                    $pageData =
                        new PageData(
                            name: $data['name'],
                            blueprint_id: (int) $data['blueprint_id'],
                            past_behavior:  PageBehavior::tryFrom($data['past_behavior']),
                            future_behavior:  PageBehavior::tryFrom($data['future_behavior']),
                        );
                } else {
                    $pageData =
                        new PageData(
                            name: $data['name'],
                            blueprint_id: (int) $data['blueprint_id'],
                        );
                }

                return app(CreatePageAction::class)
                    ->execute($pageData);
            }
        );
    }
}
