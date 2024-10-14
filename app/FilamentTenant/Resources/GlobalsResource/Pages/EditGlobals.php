<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\Filament\Livewire\Actions\CustomPageActionGroup;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Actions\CreateGlobalTranslationAction;
use Domain\Globals\Actions\UpdateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Models\Locale;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Livewire\Redirector;
use Throwable;

class EditGlobals extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = GlobalsResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),

            'other_page_actions' => CustomPageActionGroup::make([

                Action::make('createTranslation')
                    ->color('secondary')
                    ->slideOver(true)
                    ->action('createTranslation')
                    ->form([
                        Forms\Components\Select::make('locale')
                            ->options(Locale::all()->sortByDesc('is_default')->pluck('name', 'code')->toArray())
                            ->default((string) Locale::where('is_default', true)->first()?->code)
                            ->searchable()
                            ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                            ->reactive()
                            ->required(),
                    ]),
            ])
                ->view('filament.pages.actions.custom-action-group.index')
                ->setName('other_page_actions')
                ->color('secondary')
                ->label(trans('More Actions')),
            Actions\DeleteAction::make(),

        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param  \Domain\Globals\Models\Globals  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateGlobalsAction::class)->execute($record, GlobalsData::fromArray($data)));
    }

    public function createTranslation(array $data): RedirectResponse|Redirector|false
    {

        $formData = $this->form->getState();

        $formData['locale'] = $data['locale'];

        /** @var \Domain\Globals\Models\Globals */
        $record = $this->record;

        $orginalContent = $record->parentTranslation ?? $record;

        $exist = Globals::where('translation_id', $orginalContent->id)->where('locale', $data['locale'])->first();

        /** @var \Domain\Internationalization\Models\Locale */
        $locale = Locale::whereCode($data['locale'])->first();

        /** @var \Domain\Admin\Models\Admin */
        $admin = auth()->user();

        if ($exist) {

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Globals :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->send();

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Globals :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->sendToDatabase($admin);

            return false;
        }

        $globalData = GlobalsData::fromArray($formData);

        $globalTranslation = app(CreateGlobalTranslationAction::class)->execute($orginalContent, $globalData);

        Notification::make()
            ->success()
            ->title(trans('Translation Created'))
            ->body(trans('Page Translation :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
            ->sendToDatabase($admin);

        return redirect(GlobalsResource::getUrl('edit', ['record' => $globalTranslation]));
    }
}
