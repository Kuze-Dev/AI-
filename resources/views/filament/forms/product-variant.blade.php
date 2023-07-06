<x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" {{-- :label="$getLabel()" --}} :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
    :hint="$getHint()" :hint-action="$getHintAction()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">

    <div {{ $attributes->merge($getExtraAttributes())->class(['filament-forms-tree-component space-y-6 rounded-xl']) }}>
        <div x-data="{
            productVariants: null,
            state: $wire.entangle('{{ $getStatePath() }}').defer,
        }">

            <x-forms::button :wire:click="'dispatchFormEvent(\'productVariant::editItem\', \'' . $getStatePath() . '\')'"
                size="sm" type="button">
                @lang('Manage product option')
            </x-forms::button>
            {{-- @dd($getStatePath()) --}}
            {{-- <ul>
            <template x-for="item in state" :key="item.id">
                <li x-text="item.data.size"></li>
            </template>
        </ul> --}}
            <div class="rounded-lg shadow">
                <div>
                    <!-- Option List -->
                    <ul class="grid grid-cols-1 gap-2">
                        <!-- Option Item -->
                        {{-- <template x-for="item in productVariants" :key="item.id">
                        </template> --}}
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
