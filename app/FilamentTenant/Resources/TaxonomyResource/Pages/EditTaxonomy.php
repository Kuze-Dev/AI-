<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\Filament\Livewire\Actions\CustomPageActionGroup;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Internationalization\Models\Locale;
use Domain\Taxonomy\Actions\CreateTaxonomyTranslationAction;
use Domain\Taxonomy\Actions\UpdateTaxonomyAction;
use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
// use Filament\Pages\Actions;
// use Filament\Pages\Actions\Action;
// use Filament\Resources\Pages\EditRecord;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Redirector;

class EditTaxonomy extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxonomyResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            'page_actions' => CustomPageActionGroup::make([
                Action::make('createTranslation')
                    ->color('secondary')
                    ->slideOver(true)
                    ->action('createTranslation')
                    ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
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
                ->hidden((bool) tenancy()->tenant?->features()->inactive(\App\Features\CMS\Internationalization::class))
                ->label(trans('More Actions')),

        ];
    }

    /** @param  \Domain\Taxonomy\Models\Taxonomy  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // dd($data);
        return DB::transaction(fn () => app(UpdateTaxonomyAction::class)->execute($record, TaxonomyData::fromArray($data)));
    }

    public function createTranslation(array $data): RedirectResponse|Redirector|false
    {

        $formData = $this->form->getState();

        $formData['locale'] = $data['locale'];

        $code = $data['locale'];

        if ($formData['has_route']) {
            $formData['route_url']['url'] = $this->changeUrlLocale($formData['route_url']['url'], $code);
        }

        $formData['terms'] = array_map(function ($term) use ($formData) {

            $term['translation_id'] = $term['id'];

            $term['id'] = null;

            if ($formData['has_route']) {
                if ($term['url']) {
                    $term['url'] = '/'.$formData['locale'].$term['url'];
                } else {
                    $term['url'] = $formData['route_url']['url'].'/'.Str::of($term['name'])->slug();
                }
            }

            return $term;
        }, $formData['terms']);

        /** @var Taxonomy */
        $record = $this->record;

        $orginalContent = $record->parentTranslation ?? $record;

        $exist = Taxonomy::where(fn ($query) => $query->where('translation_id', $orginalContent->id)->orWhere('id', $orginalContent->id)
        )->where('locale', $data['locale'])->first();

        /** @var \Domain\Internationalization\Models\Locale */
        $locale = Locale::whereCode($data['locale'])->first();

        /** @var \Domain\Admin\Models\Admin */
        $admin = auth()->user();

        if ($exist) {

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Page :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->send();

            Notification::make()
                ->danger()
                ->title(trans('Translation Already Exists'))
                ->body(trans('Page :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
                ->sendToDatabase($admin);

            return false;
        }

        $taxonomyData = TaxonomyData::fromArray($formData);

        $taxonomyTranslation = app(CreateTaxonomyTranslationAction::class)->execute($orginalContent, $taxonomyData);

        Notification::make()
            ->success()
            ->title(trans('Translation Created'))
            ->body(trans('Page Translation :title has a existing ( :code ) translation', ['title' => $record->name, 'code' => $locale->name]))
            ->sendToDatabase($admin);

        return redirect(TaxonomyResource::getUrl('edit', ['record' => $taxonomyTranslation]));
    }

    protected function changeUrlLocale(string $url, string $locale): string
    {

        $locales = Locale::pluck('code')->toArray();

        // Remove leading and trailing slashes from the URL
        $url = trim($url, '/');

        // Split the URL by "/"
        $segments = explode('/', $url);

        // Check if the first segment is a valid locale code from the array
        if (in_array($segments[0], $locales)) {
            // Replace the existing locale with the new one
            $segments[0] = $locale;
        } else {
            // Prepend the new locale to the URL
            array_unshift($segments, $locale);
        }

        // Rebuild the URL and add a leading "/"
        return '/'.implode('/', $segments);
    }

    protected function getRedirectUrl(): ?string
    {
        return TaxonomyResource::getUrl('edit', [$this->record]);
    }
}
