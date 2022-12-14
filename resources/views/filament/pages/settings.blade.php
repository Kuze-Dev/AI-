<x-filament::page>
    <div class="grid grid-cols-1   lg:grid-cols-2   filament-forms-component-container gap-6">
        @foreach ($settings as $setting)
            @if(!$setting::shouldShowSettingsCard())
                @continue
            @endif
            <div class=" col-span-1">
                <div class="filament-forms-card-component dark:border-gray-600 dark:bg-gray-800 p-6 bg-white rounded-xl border border-gray-300">
                    <div class="grid grid-cols-1 filament-forms-component-container gap-6">
                        <div class="col-span-1">
                            <div class="filament-forms-field-wrapper">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between space-x-2 rtl:space-x-reverse">
                                        <label class="filament-forms-field-wrapper-label inline-flex items-center space-x-3 rtl:space-x-reverse" for="group">
                                            <x-dynamic-component class="h-8" :component="$setting::getNavigationIcon()" />
                                            <span class="text-lg dark:text-white font-medium">
                                                {{ trans(':name Settings', ['name' => str($setting::getSettings()::group())->headline()]) }}
                                            </span>
                                        </label>
                                        <div>
                                            <a class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-page-button-action"
                                                href="{{ $setting::getUrl() }}">
                                                {{ trans('Update') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament::page>
