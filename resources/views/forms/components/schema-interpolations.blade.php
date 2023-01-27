<x-dynamic-component
    :component="$getFieldWrapperView()"
    :state-path="$getStatePath()"
>
    @php
        $schemaData = $getSchemaData();
    @endphp

    <code>
        @if ($schemaData !== null)
            @foreach($schemaData->sections as $section)
                @foreach ($section->fields as $field)
                    <?php echo'{{ $'?>{{$section->state_name}}<?php echo "['";?>{{$field->state_name}}<?php echo "'] }}";?>
                    <br>
                @endforeach
            @endforeach
        @endif
    </code>
</x-dynamic-component>
