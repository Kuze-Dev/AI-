@props([
    'title' => null,
    'width' => 'md',
])

<x-filament::layouts.base :title="$title">
    <div
        @class([
            'filament-login-page flex min-h-screen items-center justify-center bg-gray-100 py-12 text-gray-900',
            'dark:bg-gray-900 dark:text-white' => config('filament.dark_mode'),
        ])
    >
        <div
            @class([
                '-mt-16 w-screen space-y-8 px-6 md:mt-0 md:px-2',
                match ($width) {
                    'xs' => 'max-w-xs',
                    'sm' => 'max-w-sm',
                    'md' => 'max-w-md',
                    'lg' => 'max-w-lg',
                    'xl' => 'max-w-xl',
                    '2xl' => 'max-w-2xl',
                    '3xl' => 'max-w-3xl',
                    '4xl' => 'max-w-4xl',
                    '5xl' => 'max-w-5xl',
                    '6xl' => 'max-w-6xl',
                    '7xl' => 'max-w-7xl',
                    default => $width,
                },
            ])
        >
            <div
                @class([
                    'relative space-y-4 rounded-2xl border border-gray-200 bg-white/50 p-8 shadow-2xl backdrop-blur-xl',
                    'dark:border-gray-700 dark:bg-gray-900/50' => config('filament.dark_mode'),
                ])
            >
                <div class="flex w-full justify-center">
                    @if (app(App\Settings\SiteSettings::class)->logo == null)
                        <h3 class="text-center text-2xl font-bold tracking-tight">{{ app(App\Settings\SiteSettings::class)->name ?? 'Welcome!' }}</h3>
                    @else
                        <img src="{{ Storage::url(app(App\Settings\SiteSettings::class)->logo) }}">
                    @endif
                </div>


                <h3 class="text-center text-2xl font-bold tracking-tight">
                    {{ trans('HASP CMS Log in for Your Website') }}
                </h3>


                <div {{ $attributes }}>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @livewire('notifications')
</x-filament::layouts.base>
