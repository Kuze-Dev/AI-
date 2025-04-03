<?php

declare(strict_types=1);

namespace Domain\Menu\Imports;

use App\Features\CMS\Internationalization;
use Domain\Form\Actions\CreateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Domain\Menu\Actions\CreateMenuAction;
use Domain\Menu\Actions\CreateMenuTranslationAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * @property-read Form $record
 */
class MenuImporter extends Importer
{
    protected static ?string $model = Menu::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                        $data = $validator->getData();

                        if (TenantFeatureSupport::active(Internationalization::class)) {
                            $exist = Menu::where('name',$value)->where('locale',$data['locale'])->count();
                            if ($exist) {
                                return $fail("Menu name {$value} has already been taken.");
                            }
                        }else {
                            $exist = Menu::where('name',$value)->count();
                            if ($exist) {
                                return $fail("Menu name {$value} has already been taken.");
                            }
                        }

                    },


                ],
                ),

            ImportColumn::make('slug')
                ->requiredMapping(),

            ImportColumn::make('locale')
                ->requiredMapping(),

    
            ImportColumn::make('sites')
                ->requiredMapping(),

            ImportColumn::make('parent_translation')
            ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                        if ($value) {

                            $parentMenu = Menu::where('slug', $value)->count();

                            if ($parentMenu === 0) {

                                Notification::make()
                                    ->title(trans('menu Import Error'))
                                    ->body("menu {$value} Not Found.")
                                    ->danger()
                                    ->when(config('queue.default') === 'sync',
                                        fn (Notification $notification) => $notification
                                            ->persistent()
                                            ->send(),
                                        fn (Notification $notification) => $notification->sendToDatabase(filament_admin(), isEventDispatched: true)
                                    );

                                $fail("menu '{$value}' Not Found.");
                            }
                        }

                    },
                ])
                ->requiredMapping(),

            ImportColumn::make('nodes')
                ->requiredMapping(),

        ];
    }

    #[\Override]
    public function resolveRecord(): Menu
    {

        if (is_null($this->data['slug'])) {
            return new Menu;
        }

        return Menu::where('slug', $this->data['slug'])->first() ?? new Menu;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Forms
         * Creation thru Domain Level Action
         */
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {

        if ($this->record->exists) {
            return;
        }

        /** @var array $siteIDs */
        $siteIDs = (array_key_exists('sites', $this->data) && ! is_null($this->data['sites'])) ?
            Site::whereIn('domain', explode(',', $this->data['sites']))->pluck('id')->toArray() :
            [];
       
        $nodes = json_decode($this->data['nodes'], true);
        $nodes = $this->removeIds($nodes);

        $menuData = MenuData::fromArray([
            'name' => $this->data['name'],
            'sites' => $siteIDs,
            'locale' => $this->data['locale'],
            'nodes' => $nodes,
        ]);

        if ($this->data['parent_translation']) {
            $parentMenu = Menu::where('slug', $this->data['parent_translation'])->first();
            
            app(CreateMenuTranslationAction::class)->execute($parentMenu, $menuData);
            # code...
        }else {
            app(CreateMenuAction::class)->execute($menuData);
        }
    }

    private function removeIds(array $items): array {
        return array_map(function ($item) {
            if (!is_array($item)) {
                return $item; // Ensure it's an array before modifying
            }
            $item['parent_id'] = null; // Set 'parent_id' to null if it exists
            $item['menu_id'] = null; // Set 'menu_id' to null if it exists
            unset($item['id']); // Remove 'id' if it exists
    
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $item[$key] = $this->removeIds($value); // Recursively process nested arrays
                }
            }
    
            return $item;
        }, $items);
    }
    
    

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Taxonmy import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
