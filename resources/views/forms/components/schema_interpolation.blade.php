<x-dynamic-component
    :component="$getFieldWrapperView()"
    :state-path="$getStatePath()"
>
    <code>
        @if (!empty($getSchemaData()))
            @foreach($getSchemaData()['schema']['sections'] as $item)
                @foreach ($item->fields as $field)
                    <?php echo'{{ $'?>{{$item->state_name}}<?php echo "['";?>{{$field->state_name}}<?php echo "'] }}";?>
                    <br>
                @endforeach
            @endforeach
        @endif
    </code>
</x-dynamic-component>
