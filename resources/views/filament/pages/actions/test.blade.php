
@props([
'actions',
'color' => null,
'darkMode' => config('filament.dark_mode'),
'icon' => 'heroicon-o-dots-vertical',
'label' => __('filament-support::actions/group.trigger.label'),
'size' => null,
'tooltip' => null,
'tag' => 'a',
'href' => 'test',
])

<div class="grid-cols-[10rem,1fr] items-center">
    <x-filament-support::button :color="$color" :dark-mode="$darkMode" :size="$size" :tooltip="$tooltip" :tag="$tag" :href="$href">
        {{ $label }}
    </x-filament-support::button>
</div>
