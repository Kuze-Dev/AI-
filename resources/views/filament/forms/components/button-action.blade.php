@php
    $alignClass = match ($getAlignment()) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
    :hint="$getHint()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()" @class(['drop-in-action-component w-full', $alignClass])>
    <div class="drop-in-action-actions-container relative"
        @if ($isLabelHidden() && !$hasInlineLabel()) style="padding-block-end: 1px;" @endif>
        @foreach ($getExecutableActions() as $executableAction)
            <x-forms::actions.action :size="$getSize()" :action="$executableAction" @class(['flex items-center', 'w-full' => $isFullWidth()])
                component="forms::button">
                @if (!$executableAction->isLabelHidden())
                    {{ $executableAction->getLabel() }}
                @endif
            </x-forms::actions.action>
        @endforeach
    </div>
</x-dynamic-component>
