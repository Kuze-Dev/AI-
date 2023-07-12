<x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" {{-- :label="$getLabel()" --}} :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
    :hint="$getHint()" :hint-action="$getHintAction()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">

    <div {{ $attributes->merge($getExtraAttributes())->class(['filament-forms-tree-component space-y-6 rounded-xl']) }}>
        <div x-data="{
            productVariants: null,
            state: $wire.entangle('{{ $getStatePath() }}').defer,
        }">
            <div class="rounded-lg shadow">
                <div>
                    <!-- Option List -->
                    <ul class="grid grid-cols-1 gap-2">
                        <!-- Option Item -->
                        @foreach ($getState() as $key => $item)
                            @include('filament.forms.product-variant.item', [
                                'key' => $key,
                                'item' => $item,
                                'statePath' => $getStatePath() . '.' . $key,
                            ])
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-dynamic-component>
