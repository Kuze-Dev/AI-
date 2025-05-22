<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MenuResource;
use Domain\Internationalization\Models\Locale;
use Domain\Menu\Actions\CreateMenuTranslationAction;
use Domain\Menu\Actions\UpdateMenuAction;
use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class EditMenu extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = MenuResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            DeleteAction::make(),
            ActionGroup::make([

                Action::make('createTranslation')
                    ->slideOver(true)
                    ->action(fn (Action $action) => $this->createTranslation($action->getFormData()))
                    ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class))
                    ->form([
                        Forms\Components\Select::make('locale')
                            ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                            ->default((string) Locale::where('is_default', true)->first()?->code)
                            ->searchable()
                            ->hidden((bool) \Domain\Tenant\TenantFeatureSupport::inactive(\App\Features\CMS\Internationalization::class))
                            ->reactive()
                            ->required(),
                    ]),
            ])
                ->button()
                ->icon('')
                ->label(trans('More Actions')),
        ];
    }

    /** @param  \Domain\Menu\Models\Menu  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateMenuAction::class)->execute($record, MenuData::fromArray($data));
    }

    public function createTranslation(array $data): RedirectResponse|Redirector|false
    {

        $formData = $this->form->getState();

        $formData['locale'] = $data['locale'];

        $formData['nodes'] = array_map(function ($term) {

            $term['translation_id'] = $term['id'];

            $term['id'] = null;

            return $term;
        }, $formData['nodes']);

        /** @var \Domain\Menu\Models\Menu */
        $record = $this->record;

        $orginalContent = $record->parentTranslation ?? $record;

        $exist = Menu::where(fn ($query) => $query->where('translation_id', $orginalContent->id)->orWhere('id', $orginalContent->id)
        )->where('locale', $data['locale'])->first();

        /** @var \Domain\Internationalization\Models\Locale */
        $locale = Locale::whereCode($data['locale'])->first();

        $admin = filament_admin();

        if ($exist) {

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Menu :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->send();

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Menu :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->sendToDatabase($admin);

            return false;
        }

        $menuData = MenuData::fromArray($formData);

        $menuTranslation = app(CreateMenuTranslationAction::class)->execute($orginalContent, $menuData);

        Notification::make()
            ->success()
            ->title(trans('Translation Created'))
            ->body(trans('Menu Translation :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
            ->sendToDatabase($admin);

        return redirect(MenuResource::getUrl('edit', ['record' => $menuTranslation]));
    }
}
