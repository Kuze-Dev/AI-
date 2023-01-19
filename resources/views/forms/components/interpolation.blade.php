<x-dynamic-component
    :component="$getFieldWrapperView()"
    {{-- :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"--}}
    :state-path="$getStatePath()" 
>
        <div>
        <!-- Interact with the `state` property in Alpine.js -->
        @foreach($getItems()['schema']['sections'] as $item)

            @foreach ($item->fields as $field)
                <dd>
                <?php echo'{{$'?>{{$item->state_name}}<?php echo "['";?>{{$field->state_name}}<?php echo "']}}";?>
                </dd>
            @endforeach
          
        @endforeach
    </div>
</x-dynamic-component>
