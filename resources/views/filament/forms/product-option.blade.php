<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    {{-- :label="$getLabel()" --}}
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div {{ $attributes->merge($getExtraAttributes())->class([
        'filament-forms-tree-component space-y-6 rounded-xl',
    ]) }}>
        <div class="relative">
            <x-forms::button
                :wire:click="'dispatchFormEvent(\'productOption::createItem\', \'' . $getStatePath() . '\')'"
                size="sm"
                type="button"
            >
                @lang('Add product options')
            </x-forms::button>
        </div>
    </div>
</x-dynamic-component>
