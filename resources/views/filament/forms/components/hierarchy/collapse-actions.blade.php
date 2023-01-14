<div class="space-x-2 rtl:space-x-reverse" x-data="{}">
    <x-forms::link
        x-on:click="$dispatch('hierarchy-collapse', '{{ $getStatePath() }}')"
        tag="button"
        size="sm"
    >
        {{ __('forms::components.repeater.buttons.collapse_all.label') }}
    </x-forms::link>

    <x-forms::link
        x-on:click="$dispatch('hierarchy-expand', '{{ $getStatePath() }}')"
        tag="button"
        size="sm"
    >
        {{ __('forms::components.repeater.buttons.expand_all.label') }}
    </x-forms::link>
</div>
