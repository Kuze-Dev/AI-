<x-filament-widgets::widget>
    <x-filament::section class="w-full max-w-full">
        <h3 class="mb-4 text-lg font-semibold">Edit</h3>
        <hr class="mb-4" />
{{-- {{ dd($navigationItems) }} --}}
        <div class="flex flex-wrap gap-4">
            @foreach (range(1, 50) as $i)
                <a href="#"
                   class="group flex flex-col items-center justify-center space-y-2 rounded-xl border border-gray-200 bg-white p-4 text-center shadow transition duration-200 hover:bg-green-50 hover:shadow-md"
                   style="flex: 0 0 calc(20% - 1rem);"> {{-- 4 items per row with 1rem gap --}}
                        <x-dynamic-component :component="'heroicon-o-cog'" class="h-8 w-8 text-green-600 group-hover:text-green-700" />
                    <span class="text-sm font-medium text-green-800 group-hover:text-green-900">Tile {{ $i }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
