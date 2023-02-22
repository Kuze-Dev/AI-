<x-dynamic-component :component="$getFieldWrapperView()" :state-path="$getStatePath()">
    @php
        $interpolations = $getInterpolations();
    @endphp
    <div class="prose dark:prose-invert">
        @if (count($interpolations) > 0)
            <pre><code>{{ array_reduce($interpolations, fn($acc, $interpolation) => "{$acc}{$interpolation}\n", '') }}</code></pre>
        @endif
    </div>
</x-dynamic-component>
