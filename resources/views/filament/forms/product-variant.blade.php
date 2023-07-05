<x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" {{-- :label="$getLabel()" --}} :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
    :hint="$getHint()" :hint-action="$getHintAction()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }">
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
                    <li class="rounded-lg p-2 flex items-center w-full bg-transparent border border-gray-700">
                        <span class="ml-2"> Small/White</span>
                        <span class="ml-2"> (SKU 121212) (Stock 99)</span>
                    </li>

                    <!-- Option Item -->

                    <li class="rounded-lg p-2 flex items-center w-full bg-transparent border border-gray-700">
                        <span class="ml-2"> Small/Black</span>
                        <span class="ml-2"> (SKU 121212) (Stock 99)</span>
                    </li>

                    {{-- <x-filament::card>
                        <li class="rounded-lg p-4flex">
                            <span class="ml-2"> Small/Black</span>
                            <span class="ml-2"> (SKU 121212) (Stock 99)</span>
                        </li>
                    </x-filament::card> --}}

                    <!-- Option Item -->
                    <li class="rounded-lg p-2 flex items-center w-full bg-transparent border border-gray-700">
                        <span class="ml-2"> Small/Blue</span>
                        <span class="ml-2"> (SKU 121212)</span>
                        <span class="ml-2"> (Stock 99)</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</x-dynamic-component>
