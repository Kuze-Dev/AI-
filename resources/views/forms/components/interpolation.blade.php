<x-dynamic-component
    :component="$getFieldWrapperView()"
    :state-path="$getStatePath()" 
>
        <div>
        <!-- Interact with the `state` property in Alpine.js -->
        @if (!empty($getItems()))
            @foreach($getItems()['schema']['sections'] as $item)

                @foreach ($item->fields as $field)
                    <dd>
                    <?php echo'{{$'?>{{$item->state_name}}<?php echo "['";?>{{$field->state_name}}<?php echo "']}}";?>
                    </dd>
                @endforeach
            
            @endforeach
        @endif
    </div>
</x-dynamic-component>
